<?php

class Format_name implements Format {

	public function process ($input) {

		$filter = array( "name","names","called","mr","mrs","ms","mister","first","last","call","me","people","this","friend","best","brother","sister","mother","father","mom","dad" );
		
		// strip numbers
		$string = "";
		for ($i=0; $i<strlen($input); $i++) {
			$char = substr($input,$i,1);
			if (!is_numeric($char)) $string .= $char;
		}		
		
		$string = Language::killlanguage($string);				
		
		$string = trim($string);		
		
		$words = explode(" ",$string);
		foreach ($words as $key => $word) {
			$word = strtolower($word);
			foreach ($filter as $w) {
				if ($word == $w) {
					unset($words[$key]);
				}
			}
			if (isset($words[$key])) {
				$words[$key] = strtoupper(substr($word,0,1)).substr($word,1);
				if (substr($words[$key],0,2) == "Mc") {
					$words[$key] = substr($words[$key],0,2).strtoupper(substr($words[$key],2,1)).substr($words[$key],3);
				}
			}
		}
		$words = array_slice($words,0); // reset indices
		$output = implode(" ",$words);
		BH_util::log("format [name] [$input] -> [$output]");		
		return $output;
	
	}

}

?>