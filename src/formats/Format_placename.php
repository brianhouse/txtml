<?php

class Format_placename implements Format {

	public function process ($input) {
	
		$filter = array( "live","address","come","move" );		
		
		$string = Language::killlanguage($input);
	
		$words = explode(" ",$string);
		foreach ($words as $key => $word) {
			$word = strtolower($word);
			foreach ($filter as $w) {
				if ($word == $w) {
					unset($words[$key]);
				}
			}
			if (isset($words[$key])) $words[$key] = strtoupper(substr($word,0,1)).substr($word,1);
		}
		$words = array_slice($words,0); // reset indices
		$output = implode(" ",$words);
		BH_util::log("format [placename] [$input] -> [$output]");		
		return $output;
	
	}

}

?>