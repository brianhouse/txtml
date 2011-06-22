<?php

class Language {

	
	static function clean ($string) {
	
		$string = BH_util::prefix("--",$string);		
		$string = BH_util::prefix("//",$string);				
		$string = BH_util::detab($string," ");
		$string = Language::killjunk(trim($string));
		$string = Language::killemoticon($string);
		$string = str_replace("&","and",$string);
		$string = str_replace("+","and",$string);		
		$string = str_replace("@","at",$string);
		//$string = Language::makedigits($string);  // - is removed by makedigits
		$string = str_replace("-"," ",$string);
		$string = str_replace("."," ",$string);
		$string = str_replace(","," ",$string);		
		$string = BH_util::depunctuate($string,"?");
		$string = BH_util::singlespace($string);		
		$string = trim(strtolower($string));
		return $string;

	}		


	static function match ($string,$pattern) {

		BH_util::log("matching [$string] against [$pattern]");
		
		$tokens = self::getTokens($string);
		$conditions = self::getConditions($pattern);
		
		//print_r($conditions);
			
		foreach ($conditions as $group => $type) {	
			
			// match +
			foreach ($type['+'] as $key => $condition) {
				foreach ($tokens as $token) {
					if (substr($condition,0,1) == "*") {
						//echo "\t$token vs\n";
						foreach (self::syns(substr($condition,1)) as $syn) {
							//echo "\t\t$syn\n";
							if (levenshtein($token,$syn) <= floor(strlen($token) / 4.1)) $type['+'][$key] = true;
						}
					} else {
						if (levenshtein($token,$condition) <= floor(strlen($token) / 4.1)) $type['+'][$key] = true;
						//echo "\t$token vs $condition\n";
					}
				}
			}

			// screen for -
			foreach ($type['-'] as $key => $condition) {
				foreach ($tokens as $token) {
					if (substr($condition,0,1) == "*") {
						foreach (self::syns(substr($condition,1)) as $syn) {
							if (levenshtein($token,$syn) <= floor(strlen($token) / 4.1)) $type['-'][$key] = false;							
						}
					} else {
						if (levenshtein($token,$condition) <= floor(strlen($token) / 4.1)) $type['-'][$key] = false;							
					}
				}
			}
					
			$submatch = true;
			foreach ($type['+'] as $result) {
				if ($result !== true) $submatch = false;
			}			
			foreach ($type['-'] as $result) {
				if ($result === false) $submatch = false;
			}
			if (!sizeof($type['+']) && !sizeof($type['-'])) $submatch = false;
	
			$conditions[$group] = $submatch;

		}
		
		$match = false;
		foreach ($conditions as $group => $result) {
			if ($result === true) $match = true;
		}		
		
		if ($match) {
			BH_util::log("--> matched!");
			return true;
		} else {
			BH_util::log("--> not matched!");
			return false;
		}
	
	}
	
	
	static function getConditions ($pattern) {
			
		// load conditions
		$conditions = array();
		$group = 0;
		foreach (explode(",",$pattern) as $condition) {
		
			if (!strlen($condition)) continue;
			
			$conditions[$group] = array('+' => array(), '-' => array());
			
			// for each token in the group
			foreach (explode("+",$condition) as $c) {

				// determine type
				$type = substr($c,0,1) == "!" ? "-" : "+";
				if ($type == "-") $c = substr($c,1);
	
				// normalize
				$c = Language::killpossess($c);
				$c = Language::makedigits($c);	

				// load into types
				if (!empty($c)) {
					$conditions[$group][$type][] = $c;
				}

			}
			
			$group++;
			
		}					
					
		return $conditions;
	
	}
	
	
	static function getTokens ($string) {
			
		$tokens = array();

		// normalize
		$string = Language::killpossess($string);	

		// detect question
		$question = Language::question($string);
		$string = str_replace("?","",$string);
		if ($question) $tokens[] = "?";	

		// substitute negatives
		$negative = false;
		foreach (explode(" ",$string) as $token) {
			if ($negative) {
				$ant = Language::ant($token);
				if (strlen($ant)) {
					$token = $ant;
				} else {
					$tokens[] = "not";
				}
			}
			$negative = $token == "not" ? true : false;
			if (!$negative) $tokens[] = $token;
		}				
		
		BH_util::log("--> input with substitutions [".implode(" ",$tokens)."]");
	
		return $tokens;
	
	}
		
	
	static function syns ($word) {
	
		static $loaded = array();
		if (in_array($word,$loaded)) return array(); // we've already loaded this word list, return blank
		$loaded[] = $word; // add this list to our list of loaded words
		$root = func_num_args() > 1 ? false : true; // we know its the base case if theres no extra argument
		
		// check for a word list; if none, make one with just one item
		$list = @file(BH_config::basedir()."/src/language/synonyms/$word.txt");
		if (!$list) {
			if ($root) $loaded = array(); // reset the loaded array
			return array( 0 => "$word");
		}
		
		// for the list, recursively grab any sub word lists
		$syns = array();
		foreach ($list as $word) {
			$word = trim($word);
			if (!strlen($word)) continue;
			if (substr($word,0,1) == "*") {
				$syns = array_merge($syns,Language::syns(substr($word,1),false));
			} else {
				$syns[] = $word;
			}
		}
		
		if ($root) $loaded = array(); // reset the loaded array
		return sizeof($syns) ? $syns : array(); // return the result
	
	}
	
	
	static function question ($string) {
	
		if (strpos($string,"?") !== false) return true;
		$first_word = BH_util::prefix(" ",$string);
		$list = @file(BH_config::basedir()."/src/language/questions.txt");
		foreach ($list as $word) {
			$word = trim($word);
			if (!strlen($word)) continue;
			if ($first_word == $word) return true;
		}
		return false;
	
	}
	
	
	static function ant ($word) {
	
		// reduce synonyms (non-recursive)
		if (!$handle = opendir(BH_config::basedir()."/src/language/synonyms/")) return null;
		while ($synlist = readdir($handle)) {
			if (substr($synlist,0,1) == ".") continue;
			$list = @file(BH_config::basedir()."/src/language/synonyms/".$synlist);
			foreach ($list as $key => $syn) {
				$syn = trim($syn);
				if (!strlen($syn)) {
					unset($list[$key]);
					continue;
				}
				$list[$key] = $syn;					
			}
			if (in_array($word,$list)) {
				$word = BH_util::prefix(".",$synlist); // purposefully excludes *s
				break;
			}
		}

		// load ants table
		$ants = array();
		if (!$list = @file(BH_config::basedir()."/src/language/antonyms.txt")) $list = array();
		foreach ($list as $key => $value) {
			$value = trim($value);
			if (!strlen($value)) {
				unset($list[$key]);
				continue;
			}
			$pair = explode(" ",trim($value));
			if (sizeof($pair) != 2) continue;
			$ants[trim($pair[0])] = trim($pair[1]);
		}
		
		// return any matches
		if (!empty($ants[$word])) return $ants[$word];
		$ants = array_flip($ants);
		if (!empty($ants[$word])) return $ants[$word];		
		return null;
	
	}
	

	static function killjunk ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/junk.txt");
		foreach ($list as $junk) {
			$junk = trim($junk);
			if (!strlen($junk)) continue;
			$string = str_replace($junk,"",$string);
		}
		return $string;

	}
	
	static function killemoticon ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/emoticons.txt");
		foreach ($list as $pair) {
			$pair = trim($pair);
			if (!strlen($pair)) continue;
			$pair = explode(" ",$pair);			
			$emoticon = trim($pair[0]);
			$word = isset($pair[1]) ? trim($pair[1]) : "";
			$string = str_replace($emoticon,$word,$string);
		}
		return $string;

	}	
	
	
	static function killpossess ($string) {
	
		// just handles regular and almost-regular
		$words = explode(" ",$string);
		foreach ($words as $key => $word) {
			if (strlen(BH_util::depunctuate($word)) <= 3) continue;
			if (substr($word,-3) == "ies") {
				$words[$key] = substr($word,0,-3)."y";
			} else if (substr($word,-3) == "ves") {			
				$words[$key] = substr($word,0,-3)."f";			
			} else if (substr($word,-2) == "es" && strlen($word) > 3) {
				$words[$key] = substr($word,0,-2);
			} else if (substr($word,-1) == "s" && substr($word,-2,1) != "s") {
				$words[$key] = substr($word,0,-1);
			}
			if (substr($word,-2) == "ie") $words[$key] = substr($word,0,-2)."y";			// normalizes movie & puppy
		}
		$string = implode(" ",$words);
		return $string;
	
	}
	
	static function killlanguage ($string) {
	
		$string = str_replace("?","",$string);					
		$string = Language::killcopulae($string);
		$string = Language::killpronouns($string);	
		$string = Language::killprepositions($string);
		$string = Language::killsalutations($string);			
		$string = Language::killadverbs($string);
		$string = Language::killopinions($string);		
		$string = Language::killquestions($string);		
		return $string;
	
	}
	
	static function killcopulae ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/copulae.txt");
		return Language::killlist($string,$list);
	
	}


	static function killpronouns ($string) {

		$list = @file(BH_config::basedir()."/src/language/pronouns.txt");
		return Language::killlist($string,$list);
		
	}
	
	
	static function killprepositions ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/prepositions.txt");
		return Language::killlist($string,$list);
	
	}	
	
	
	static function killsalutations ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/salutations.txt");
		return Language::killlist($string,$list);
	
	}	

	static function killopinions ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/opinions.txt");
		return Language::killlist($string,$list);
	
	}		

	static function killquestions ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/questions.txt");
		return Language::killlist($string,$list);
	
	}		

	static function killadverbs ($string) {
	
		$list = @file(BH_config::basedir()."/src/language/adverbs.txt");
		return Language::killlist($string,$list);
	
	}		
	
	static function killlist ($string,$list) {
	
		$tokens = explode(" ",$string);			
		foreach ($list as $word) {
			$word = strtolower(trim($word));
			if (!strlen($word)) continue;
			foreach ($tokens as $key => $token) {
				if ($token == $word) unset($tokens[$key]);
			}
		}	
		$string = implode(" ",$tokens);
		return $string;	
	
	}
	
	
	static function makenumeric ($string) {

		$string = trim($string);	
		$string = str_replace("st","",$string);
		$string = str_replace("nd","",$string);
		$string = str_replace("rd","",$string);		
		$string = str_replace("th","",$string);
		for ($i=0; $i<strlen($string); $i++) {
			if (!is_numeric(substr($string,$i,1)) && substr($string,$i,1) != ".") {
				$string = substr($string,0,$i) . substr($string,++$i);				
			}
		}
		$string = BH_util::prefix(" ",$string);		
		return strval(floatval($string)); // must preserve as a string
	
	}
	

	static function makedigits ($string) {
	
		$numbers = Array (
								"zero"		=>	"0",
								"one"		=>	"1",
								"two"		=>	"2",
								"three"		=>	"3",
								"four"		=>	"4",
								"five"		=>	"5",
								"six"		=>	"6",
								"seven"		=>	"7",
								"eight"		=>	"8",
								"nine"		=>	"9",
								"ten"		=>	"10",
								"eleven"	=>	"11",
								"twelve"	=>	"12",
								"thirteen"	=>	"13",
								"fourteen"	=>	"14",
								"fifteen"	=>	"15",
								"sixteen"	=>	"16",
								"seventeen"	=>	"17",
								"eighteen"	=>	"18",
								"nineteen"	=>	"19",
								"twenty"	=>	"20",
								"thirty"	=>	"30",
								"forty"		=>	"40",
								"fifty"		=>	"50",
								"sixty"		=>	"60",
								"seventy"	=>	"70",
								"eighty"	=>	"80",
								"ninety"	=>	"90",
								"hundred"	=>	"00",
								"thousand"	=>	"000",
								"million"	=>	"000000"
							);

		$numbers = array_reverse($numbers);					
	
		foreach ($numbers as $number => $digits) {
			$string = str_replace($number,$digits,$string);
		}

		$last_key = null;		
		$last_number = null;	
		$string = str_replace("-"," ",$string);
		$tokens = explode(" ",$string);
		foreach ($tokens as $key => $token) {
			if (in_array($token,$numbers)) {	
				$tail = substr($last_number,0-strlen($token));
				if ($last_key !== null && strlen($tail) && str_replace("0","",$tail) == "") {
						$last_number = substr($last_number,0,0-strlen($token));
						$tokens[$last_key] = $last_number = $last_number.$token;
						unset($tokens[$key]);						
				} else if ($last_key !== null && substr($token,0,1) == "0") {
						if (substr($last_number,-1) == 0) $last_number = substr($last_number,0,-1);
						$tokens[$last_key] = $last_number = $last_number.$token;
						unset($tokens[$key]);		
				} else {
					$last_key = $key;
					$last_number = $token;
				}
			} else {
				if ($last_key !== null && $token == "and") {
					unset($tokens[$key]);	
				} else {
					$last_key = null;
					$last_number = null;
				}
			}
		}
		
		$string = implode(" ",$tokens);
		return $string;
	
	
		// 1 million eight hundred and five will generate 100000805, which is one 0 too many, because it didnt look ahead to the hundred
		// could use some levens, but numbers are very alike
	
	}
	
	
	static function filterGSM ($string) {
	
		return $string;
	
	}

	
}

?>