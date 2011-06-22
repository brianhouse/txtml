<?php

class Module_set extends Module {

	protected function x ($user,$time,$input) {

		if ($input === null) $input = ""; // if we're not in an input handler, there is no input		
		$string = "";
		if ($this->param("value") !== null) $string = $this->param("value");
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}		

		if ($string === "") $string = $input;
		if ($string === "") return true;

		if ($this->param("filter")) {		
			$filter = explode(",",$this->param("filter"));							
			$words = explode(" ",$string);
			foreach ($words as $key => $word) {
				$word = strtolower($word);
				foreach ($filter as $w) {
					if ($word == $w) {
						unset($words[$key]);
					}
				}
			}
			$words = array_slice($words,0); // reset indices
			$string = implode(" ",$words);		
		}		

		$class = $this->param("format") ? "Format_".$this->param("format") : "Format_normal"; // why was this set on lowercase before?
		$format = new $class();
		$string = $format->process($string);				
		
		$length = (int)$this->param("length");
		if ($length && $length < strlen($string)) {
			$string = substr($string,0,$length);
		}
						
		$user->setVar($this->param("var"),$string);

		return $string;
	
	}

}

?>