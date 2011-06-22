<?php

class Format_cuss implements Format {

	public function process ($input) {
	
		$string = Language::killlanguage($input);
		$output = "";	
			
		$list = @file(BH_config::basedir()."/src/language/synonyms/cuss.txt");
		foreach (explode(" ",$string) as $word) {
			if (in_array($word."\n",$list)) {
				$output = $word;
				break;
			}
		}
				
		BH_util::log("format [cuss] [$input] -> [$output]");				
		return $output;
	
	}

}

?>