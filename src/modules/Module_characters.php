<?php

class Module_characters extends Module {

	protected function x ($user,$time,$input) {

		$string = "";
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}
		if ($string === "") $string = $input;		
		if ($string === "") return true;
			
		return strlen($string);
	
	}
	
}

?>