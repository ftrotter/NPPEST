<?php
	// Provides zip_codes and citys arrays which contain what you might expect.
	// Each Zip code is mapped to one and only one city here...
	// which might produce some interesting results!!
	
	if(isset($argv[1])){
		$file = $argv[1];
		$my_file = $argv[2];
	}else{
		$file = "zipcode/zipcode.csv";	
		$my_file = "zipcode/myzip.csv"; //this is where my additions come in...	
	}

	if(!file_exists($file)){
		echo "I need the name of the zipcode download csv file as input...\n";
		exit();
	}


	$structure_map = array();
	$file_handle = fopen($file,"r");
	$structure = fgetcsv($file_handle, 100000, ",");
	foreach($structure as $id => $col_name){
		$parens = array ( '(', ')');
		$tmp = str_replace($parens,"",strtolower($col_name));
		$value = str_replace(' ','_',$tmp);
		$structure_map[$id] = $value;
	}

	$zip_codes = array();
	$citys = array();
	while (($data_line = fgetcsv($file_handle, 10000, ",")) !== FALSE) {
		if(count($data_line) > 1){ //i.e. not a blank line..
			$mapped_line = array();
			foreach($data_line as $id => $data){
				if(strlen($data) > 0){
					//create an array of fields => data
					//NOTE what is the equiv of mysql_real_escape_string in neo4j????
					$mapped_line[$structure_map[$id]] = $data;	

				}//ifstrelen
			}//foreach

			$zip_codes[$mapped_line['zip']] = $mapped_line;
			$citys[strtolower($mapped_line['city']." ". $mapped_line['state'])][] = $mapped_line;
		}
	}

	$file_handle = fopen($my_file,"r");
	while (($data_line = fgetcsv($file_handle, 10000, ",")) !== FALSE) {
		if(count($data_line) > 1){ //i.e. not a blank line..
			$mapped_line = array();
			foreach($data_line as $id => $data){
				if(strlen($data) > 0){
					//create an array of fields => data
					//NOTE what is the equiv of mysql_real_escape_string in neo4j????
					$mapped_line[$structure_map[$id]] = $data;	

				}//ifstrelen
			}//foreach

			$zip_codes[$mapped_line['zip']] = $mapped_line;
			$citys[strtolower($mapped_line['city']." ". $mapped_line['state'])][] = $mapped_line;
		}
	}
	


?>
