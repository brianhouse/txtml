<?php

class Module_format extends Module {

	protected function x ($user,$time,$input) {

		$string = "";
		if ($this->param("value") !== null) $string = $this->param("value");
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}
		if ($string === "") return true;		

		$class = $this->param("format") ? "Format_".$this->param("format") : "Format_lowercase";
		$format = new $class();
		$string = $format->process($string);

		return strlen($string) ? $string : true;
	
	}

}

?>