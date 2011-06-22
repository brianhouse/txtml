<?php

class Feeder_moon extends Feeder {

	public function update (Feed $feed) {
	
		$rss = BH_feed::get("http://interglacial.com/rss/moon_phase.rss");
		if (!$rss) return;

		foreach ($rss->items as $item) {
			$content = (string)$item['title'];
			break;
		}

		$content = str_replace("moon","",$content);
		$content = str_replace("a ","",$content);		
		$content = trim($content);
		
		if ($content != "new" &&
			$content != "waxing quarter" &&
			$content != "waning quarter" &&
			$content != "waxing half" &&
			$content != "waning half" &&
			$content != "waxing three-quarters" &&
			$content != "waning three-quarters" &&			
			$content != "full") $content = "hidden";			
		
		$feed->setVar("phase",$content);
		$feed->setVar("last_update",strval(time()));		
		
	}

}

?>