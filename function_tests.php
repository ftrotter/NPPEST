<?php
	require_once('npi_functions.php');

	$npi_tests = array(
		1265791362, //a list of Dr Magids
		1154567816,
		1972512671,
		1780647800,
		1730187519,
		1497986384,
		1992931091, //these should all pass, then are real NPI data..
		199293109, //this should fail it is too short
		19929310911, //this should fail it is too long
		'1992931bob', //this should fail it is non-numeric (look at the O/0 change)
		'1992931O91', //this should fail it is non-numeric (look at the O/0 change)
		1992931099, //this should fail. it has the wrong check digit...
		1992931098, //this should fail. it has the wrong check digit...
		1992931097, //this should fail. it has the wrong check digit...
		1992931096, //this should fail. it has the wrong check digit...
		1992931095, //this should fail. it has the wrong check digit...
		1992931094, //this should fail. it has the wrong check digit...
		1992931093, //this should fail. it has the wrong check digit...
		1992931092, //this should fail. it has the wrong check digit...
		1992931091, //this should pass showing that only one check digit is right...
		);

	foreach($npi_tests as $maybe_npi){
		if(is_valid_npi($maybe_npi,false,true)){
			echo "$maybe_npi is valid \n";
		}
	}

?>
