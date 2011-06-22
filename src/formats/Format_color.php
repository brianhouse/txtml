<?php

class Format_color implements Format {

	public function process ($input) {
	
		$colors = array("red", "blue", "yellow", "green", "purple", "orange", "brown", "turquoise", "amber", "pink", "fuchsia", "aqua", "aquamarine", "black", "white", "grey", "gray", "azure", "cyan", "magenta", "blond", "chocolate", "lavender", "tan", "violet", "crimson", "saffron", "lime", "coral", "salmon", "maroon", "taupe", "beige", "mustard", "olive", "cerulean", "rose", "platinum", "puse", "rust", "seashell", "slate", "tangerine", "teal", "gold", "silver", "bronze", "hooloovoo", "none");

		$words = explode(" ",$string);
		foreach ($words as $key => $word) {
			$word = strtolower($word);
			foreach ($colors as $color) {
				if ($word == $color) {
					break 2;
				}
			}
		}	
		
		if ($color == "none") {
			$output = Language::killlanguage($input);			
		} else {	
			$output = $color;		
		}
		
		BH_util::log("format [color] [$input] -> [$output]");		
		return $output;
	
	}

}

?>