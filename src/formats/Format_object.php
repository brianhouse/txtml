<?php

class Format_object implements Format {

	public function process ($input) {
	
		$string = Language::killlanguage($input);
		
		$output = $string;
		
		BH_util::log("format [object] [$input] -> [$output]");				
		return $output;
	
	}

}

?>