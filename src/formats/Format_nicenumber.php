<?php

class Format_nicenumber implements Format {

	public function process ($input) {
	
		$string = Language::killlanguage($input);
		$string = Language::makedigits($string);				
		$output = Language::makenumeric($string);		
		
		$output = BH_util::addcommas($output);
		
		BH_util::log("format [nicenumber] [$input] -> [$output]");				
		return $output;
	
	}

}

?>