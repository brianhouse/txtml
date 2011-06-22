<?php

class Feeder_weathernyc extends Feeder {

	public function update (Feed $feed) {
	
		$area_code = "10004"; // lower manhattan

		$rss = BH_feed::get("http://rss.weather.com/weather/rss/local/$area_code?cm_ven=LWO&cm_cat=rss&par=LWO_rss");
		if (!$rss) return;		
		$items = array_slice($rss->items,0,1);
		$item = $items[0];
		
		$content = strip_tags($item['summary']);
		$content = str_replace("For more details?","",$content);
		$content = strtolower($content);
		
		$degrees = "";
		for ($i=0; $i<strlen($content); $i++) {
			if (is_numeric(substr($content,$i,1))) {
				$degrees .= substr($content,$i,1);
			}
		}
		
		if ($degrees != "") {
			if ($degrees < 33) {
				$temp = "freezing";
			} else if ($degrees < 40) {
				$temp = "cold";
			} else if ($degrees < 60) {
				$temp = "cool";
			} else if ($degrees < 80) {
				$temp = "warm";
			} else {
				$temp = "hot";
			}
		} else {
			if ($feed->getVar("temp") === null) $temp = "mild";
		}
				
		$description = strtolower(BH_util::prefix(",",$content));
		$sun = strpos($description,"sun") !== false ? "true" : "false";
		$rain = strpos($description,"rain") !== false ? "true" : "false";
		$snow = strpos($description,"snow") !== false ? "true" : "false";
		$clouds = strpos($description,"cloud") !== false ? "true" : "false";		
		$wind = strpos($description,"wind") !== false ? "true" : "false";
		$haze = strpos($description,"haze") !== false ? "true" : "false";		

		$feed->setVar("sun",$sun);
		$feed->setVar("temp",$temp);
		$feed->setVar("rain",$rain);
		$feed->setVar("snow",$snow);
		$feed->setVar("clouds",$clouds);
		$feed->setVar("wind",$wind);	
		$feed->setVar("haze",$haze);			
		$feed->setVar("last_update",strval(time()));		

	}

}

?>