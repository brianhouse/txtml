<?php

class Format_abc implements Format {

	public function process ($input) {
	
		$string = Language::killlanguage($input);
		
		$output = strtoupper(substr($string),0,1);
		if (	$output != "A" &&
				$output != "B" &&
				$output != "C"	) $output = "-";
	
		BH_util::log("format [abc] [$input] -> [$output]");				
		return $output;
	
	}

}

?>