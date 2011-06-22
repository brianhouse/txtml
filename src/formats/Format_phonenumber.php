<?php

class Format_phonenumber implements Format {

	public function process ($input) {
	
		$input = Language::killlanguage($input);
		
		$string = "";
		for ($i=0; $i<strlen($input); $i++) {
			$char = substr($input,$i,1);
			if (is_numeric($char)) $string .= $char;
		}

		$pattern = "/^1?[0-9]{10}$/";
		preg_match($pattern,$string,$matches);
		if (sizeof($matches)) {
			$output = $matches[0];
		} else {
			$output = "";
		}
		
		BH_util::log("format [number] [$input] -> [$output]");				
		return $output;
	
	}

}

?>