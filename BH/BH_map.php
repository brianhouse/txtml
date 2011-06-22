<?php

class BH_map {


	static function geocode ($address,$city,$state) {
	
		$substitutions = array(	"st"	=>	"street",
								"ave"	=> 	"avenue",
								"n"		=>	"north",
								"s"		=>	"south",
								"e"		=>	"east",
								"w"		=>	"west",
								"ne"	=>	"northeast",
								"nw"	=>	"northwest",
								"se"	=>	"southeast",
								"sw"	=>	"southwest",
								"+"		=>	"@",					
								"and"	=>	"@",
								"at"	=>	"@",
								"hwy"	=>	"highway",
								"rd"	=>	"road",
								"pl"	=>	"place",
								"dr"	=>	"drive",
								"blvd"	=> 	"boulevard",
								"sq"	=>  "square",
							);
							
		$numbers = array (		"1"		=>	"st",
								"2"		=>	"nd",
								"3"		=>	"rd",
								"4"		=>	"th",
								"5"		=>	"th",
								"6"		=>	"th",
								"7"		=>	"th",
								"8"		=>	"th",
								"9"		=>	"th",
								"0"		=>	"th"
							);
		
		$address = explode(" ",$address);
		foreach ($address as $key => $word) {
			foreach ($substitutions as $abrv => $replacement) if ($word == $abrv) $address[$key] = $replacement;
			foreach ($numbers as $last => $suffix) {
				if (strcmp(substr($word,-1),$last) == 0) {
					if (substr($word,-2,1) == "1") {
						$address[$key] = $address[$key]."th";
					} else {
						$address[$key] = $address[$key].$suffix;
					}
				}
			}
		}
		$address = implode(" ",$address);
		$address = trim(str_replace("  "," ",$address));
		$address .= ", $city, $state";
		$address = strtolower($address);
		
		$target = "http://geocoder.us/service/csv/geocode?address=".urlencode($address);
		$data = BH_util::scrape($target);
		$data = explode(",",$data);
		$result['lon'] = isset($data[1]) && is_numeric($data[1]) ? $data[1] : null;
		$result['lat'] = isset($data[0]) && is_numeric($data[0]) ? $data[0] : null;	
		$result['address'] = $address;
		if ($result['lon'] == null || $result['lat'] == null) return null;
		return $result;
	
	}


}

?>