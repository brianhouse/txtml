<?php

class Module_shuffle extends Module {

	protected function x ($user,$time,$input) {

		$string = "";
		if ($this->param("value") !== null) $string = $this->param("value");
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}
		if ($string === "") return true;
			
		return str_shuffle($string);
	
	}
	
}

?>