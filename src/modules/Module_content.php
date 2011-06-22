<?php

class Module_content extends Module {

	protected function x ($user,$time,$input) {
	
		$string = $this->param("string");	
		return strlen($string) ? $string : true;
	
	}

}

?>