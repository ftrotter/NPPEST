<?php
	
	require_once('filter.php');
	require_once('taxonomy.php');
	require_once('npi_functions.php');
	require_once('zipload.php'); //brings zip_codes and citys arrays into memory... allowing for zip sanity!!

	$export_errors = false; //do a var_export on any line with an error...


	if(isset($argv[1])){
		$file = $argv[1];
	}else{
		$file = "data/last_10000.csv";	
	}

	if(!file_exists($file)){
		echo "I need the name of the NPPESS download csv file as input...\n";
		exit();
	}

	$taxonomies = array();
	$structure_map = array();
	$file_handle = fopen($file,"r");
	$structure = fgetcsv($file_handle, 100000, ",");
	if(!strcmp($structure[0],'NPI') == 0){
		die("You need to have the column descriptors as the first line in your data file");
	}
	foreach($structure as $id => $col_name){
		$parens = array ( '(', ')');
		$tmp = str_replace($parens,"",strtolower($col_name));
		$value = str_replace(' ','_',$tmp);
		$structure_map[$id] = $value;
	}

	$new_data_lines = array();

	while (($data_line = fgetcsv($file_handle, 100000, ",")) !== FALSE) {
//	$data_line = fgetcsv($data_handle, 100000, ",");// just get the one.
		$had_error = false;
		$npi = $data_line['0'];
		if(strcmp($npi,'NPI')==0){
			//then this is the header record.
			continue;
		}

		$mapped_line = array();
		foreach($data_line as $id => $data){
			if(strlen($data) > 0){
				//create an array of fields => data
				//NOTE what is the equiv of mysql_real_escape_string in neo4j????
				$mapped_line[$structure_map[$id]] = $data;	

			}//ifstrelen
		}//foreach


		if(!is_valid_npi($npi,false,true)){
			echo "npi:$npi is not a valid npi\n";	
			$had_error = true;
		}else{
			//echo "$npi is ok\n";
		}

		if(isset($mapped_line['npi_deactivation_date'])){
			//then this is a deactivated NPI record. 
			//the only thing to check is whether the date is valid...
			if(!is_valid_date($mapped_line['npi_deactivation_date'])){
				echo "npi:$npi is deactivated, but has an invalid date ".$mapped_line['npi_deactivation_date']."\n";
				$had_error = true;
			}
				

		}else{ //These tests apply to all active npi records...
		

			if($mapped_line['provider_business_practice_location_address_country_code_if_outside_u.s.'] == 'US'){
				if(!is_numeric_zipcode($mapped_line['provider_business_practice_location_address_postal_code'])){
					echo "npi:$npi practice zipcode ".$mapped_line['provider_business_practice_location_address_postal_code']." is non_numeric\n";
					$had_error = true;
				}

				if(!is_length_ok_zipcode($mapped_line['provider_business_practice_location_address_postal_code'])){
					echo "npi:$npi practice zipcode ".
						$mapped_line['provider_business_practice_location_address_postal_code'].
						" is has an invalid number of digits\n";
					$had_error = true;
				}

				if(!is_city_match_zipcode(
						$mapped_line['provider_business_practice_location_address_postal_code'],
						$mapped_line['provider_business_practice_location_address_city_name'],
						$mapped_line['provider_business_practice_location_address_state_name'],
						$zip_codes
					)){
					echo "npi:$npi zip and city/state mismatch ".
						$mapped_line['provider_business_practice_location_address_postal_code']. " ".
						$mapped_line['provider_business_practice_location_address_city_name']. " ".
						$mapped_line['provider_business_practice_location_address_state_name']. " ".
						" do not match their entries in the zip db\n";
					$had_error = true;
				}



			}

			if($mapped_line['provider_business_mailing_address_country_code_if_outside_u.s.'] == 'US'){
				if(!is_numeric_zipcode($mapped_line['provider_business_mailing_address_postal_code'])){
					echo "npi:$npi mailing zipcode ".$mapped_line['provider_business_mailing_address_postal_code']." is non_numeric\n";
					$had_error = true;
				}

				if(!is_length_ok_zipcode($mapped_line['provider_business_mailing_address_postal_code'])){
					echo "npi:$npi mailing zipcode ".$mapped_line['provider_business_mailing_address_postal_code']
						." has an invalid number of digits\n";
					$had_error = true;
				}


			}

	
		} //end tests for active npi records...

		if($had_error && $export_errors){
			var_export($mapped_line);
		}
	}


function is_city_match_zipcode($zip,$city,$state,$zip_db){
	$city = strtolower($city);
	$state = strtolower($state);

	if(strlen($zip) == 9){
		//our database only considers 5 numbers...
		$zip = substr($zip,0,5);
	}
	
	if(isset($zip_db[$zip])){

		$return_me = true;
	
		$db_state = strtolower($zip_db[$zip]['state']);
		$db_city = strtolower($zip_db[$zip]['city']);
		
		if(strcmp($db_state,$state) != 0){
			$return_me = false;
			echo "State mismatch npi_state:$state and db_state:$db_state for $zip\n";
		}			

		if(strcmp($db_city,$city) != 0){
			$return_me = false;
			echo "City mismatch npi_city:$city and db_city:$db_city for $zip \n";
		}			
		
		return($return_me);

	}else{
		echo "ERROR: No entry found for $zip in the zip code database\n";
		return false;
	
	}



}

function is_valid_date($date){
	list($month,$day,$year) = explode('/',$date);
	return(checkdate($month,$day,$year));
}


function is_length_ok_zipcode($this_zip){

	if(strlen($this_zip) == 9 || strlen($this_zip) == 5){
		return true;
	}else{
		return false; // the american zipcode is either 5 digits or 9...
	}
}


function is_numeric_zipcode($this_zip){


	if(!is_numeric($this_zip)){
		return false;
	}else{
		return true;
	}	

}



function filter($mapped_line,$filter){

	$new_array = array();
	foreach($filter as $old_name => $new_name){

		if(isset($mapped_line[$old_name])){
			//then we map...
			$new_array[$new_name] = $mapped_line[$old_name];

		}else{
			$new_array[$new_name] = '';
		}

	}

	return($new_array);

}

function search_name($mapped_line){

	$name = '';
	if($mapped_line['entity_type_code'] == 1){
		//then this is a person...
		$name .= $mapped_line['provider_last_name_legal_name'] . " ";
	}else{
		$name .= $mapped_line['provider_organization_name_legal_business_name'];
	}

	return($name);

}

function simple_name($mapped_line){

	$name = '';
	if($mapped_line['entity_type_code'] == 1){
		//then this is a person...

		if(isset($mapped_line['provider_name_prefix_text'])){
			$name .= $mapped_line['provider_name_prefix_text'] . " ";
		}
		$name .= $mapped_line['provider_first_name'] . " ";
		if(isset($mapped_line['provider_middle_name'])){
			$name .= $mapped_line['provider_middle_name'] . " ";
		}
		$name .= $mapped_line['provider_last_name_legal_name'] . " ";
		if(isset($mapped_line['provider_name_prefix_text'])){
			$name .= $mapped_line['provider_credential_text'] . " ";
		}

	}else{

		$name .= $mapped_line['provider_organization_name_legal_business_name'];

	}


	return($name);

}

?>
