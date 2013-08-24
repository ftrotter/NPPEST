<?php

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}



	function is_valid_npi($npi_candidate, $throw_instead_of_return = false, $echo_error = false){

                if(!is_int($npi_candidate)){
			if(is_numeric($npi_candidate)){
				$npi_candidate = intval($npi_candidate);
			}
		}
                if(!is_int($npi_candidate)){
                        $error = "This NPI is not numeric: $npi_candidate";
                        if($throw_instead_of_return){
                                throw new Exception($error);
                        }else{
				if($echo_error){
					echo "$error <br>\n";
				}
                                return(false);
                        }
                }


		if(strlen("$npi_candidate") != 10){
			$wrong_len = strlen("$npi_candidate");
			$error = "This NPI is not 10 digits, it has $wrong_len: $npi_candidate";
			if($throw_instead_of_return){
				throw new Exception($error);	
			}else{
				if($echo_error){
					echo "$error <br>\n";
				}
				return(false);
			}
		}
		
		//We implement the Luhn check digit method, but we prepend 80840 as documented here:
		//http://www.cms.gov/Regulations-and-Guidance/HIPAA-Administrative-Simplification/NationalProvIdentStand/downloads/npicheckdigit.pdf


		$my_check_digit = substr("$npi_candidate",9,1); //returns the 10th check digit
		$npi_candidate_without_check_digit = substr("$npi_candidate",0,-1); //returns everything except the 10th check digit 
		$my_check_string = "80840" . "$npi_candidate_without_check_digit" . "$my_check_digit"; //roundabout but clear

		if(!luhn($my_check_string)){
                       $error = "This NPI is does not pass luhn: $npi_candidate";
		       if($throw_instead_of_return){
                                throw new Exception($error);
                        }else{
				if($echo_error){
					echo "$error <br>\n";
				}
                                return(false);
                        }	
		}

		return(true);

	}



// from http://www.php.net/manual/en/ref.math.php#109457
function luhn($num){ 
    if(!$num) 
        return false; 
    $num = array_reverse(str_split($num)); 
    $add = 0; 
    foreach($num as $k => $v){ 
        if($k%2) 
            $v = $v*2; 
        $add += ($v >= 10 ? $v - 9 : $v); 
    } 
    return ($add%10 == 0); 
} 



?>
