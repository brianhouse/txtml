<?php

class Module_filter extends Module {

	protected function x ($user,$time,$input) {

		$string = "";
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}
		if ($string === "") return true;
		
		if ($this->param("words")) {
		
			$filter = explode(",",$this->param("words"));
							
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
		
		$f = $this->param("chars");
		for ($i=0; $i<strlen($f); $i++) {
			$string = str_replace(substr($f,$i,1),"",$string);
		}
		
		BH_util::log("--> filtered [$string]");
		
		return $string;
	
	}
	
}

?>