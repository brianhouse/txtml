<?php

class Format_lowercase implements Format {

	public function process ($input) {
	
		$output = strtolower($input);
		BH_util::log("format [lowercase] [$input] -> [$output]");		
		return $output;
	
	}

}

?>