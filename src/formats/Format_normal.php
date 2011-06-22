<?php

class Format_normal implements Format {

	public function process ($input) {
	
		$output = $input;
		BH_util::log("format [normal]");		
		return $output;
	
	}

}

?>