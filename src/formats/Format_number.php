<?php

class Format_number implements Format {

	public function process ($input) {
	
		$string = Language::killlanguage($input);
		$string = Language::makedigits($string);		
		
		BH_util::log("----> $string");
		
		$output = Language::makenumeric($string);		
		
		BH_util::log("format [number] [$input] -> [$output]");				
		return $output;
	
	}

}

?>