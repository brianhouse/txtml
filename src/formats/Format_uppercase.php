<?php

class Format_uppercase implements Format {

	public function process ($input) {
	
		$output = strtoupper($input);
		BH_util::log("format [uppercase] [$input] -> [$output]");		
		return $output;
	
	}

}

?>