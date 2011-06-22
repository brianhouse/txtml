<?php

class Feeder_movies extends Feeder {

	public function update (Feed $feed) {
	
		$rss = BH_feed::get("http://movies.com/xml/rss/intheaters.xml");
		if (!$rss) return;
		
		$movies = array();
		
		foreach ($rss->items as $item) {		
			$title = strtolower( trim( BH_util::prefix("-",$item['title']) ) );
			if ($title) {
				$title = str_replace("&","and",$title);
				if (is_numeric(substr($title, 0, 1))) {					
					$title = explode(" ",$title);									
					$title = array_slice($title, 1);
					$title = implode(" ",$title);
				}
				$movies[] = $title;
			}
		}
		
		for ($i=1; $i<=5; $i++) {
			if (isset($movies[$i])) {
				$feed->setVar("movie_".$i,$movies[$i]);					
			}
		}
		$feed->setVar("last_update",strval(time()));		
	
	}

}

?>