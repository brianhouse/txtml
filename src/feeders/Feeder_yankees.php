<?php

class Feeder_yankees extends Feeder {

	public function update (Feed $feed) {
	
		$rss = BH_feed::get("http://www.totallyscored.com/rss");
		if (!$rss) return;

//		$scores = array();

		$updated = false;

		foreach ($rss->items as $item) {

			$info = $item['title'];
			if (strpos($info,"Major League Baseball") === false) continue;
/*			
			if ($s = strpos($info,"NHL")) {  // not === because league is never at start of string
				$sport = "hockey";
			} else if ($s = strpos($info,"National Basketball Association")) {
				$sport = "basketball";
			} else if ($s = strpos($info,"Major League Baseball")) {
				$sport = "baseball";		
			} else if ($s = strpos($info,"NFL")) {			
				$sport = "football";			
			} else {
				continue;
			}
*/			
			$time = strtotime($item['pubdate']);
			$days_ago = ceil((time() - $time) / 86400);
			if ($days_ago == 1) {
				$when = "last night";
//			} else if ($days_ago == 2) {
//				$when = "the other night";
			} else {
				continue;
			}

			$s = strpos($info,"Major League Baseball");
			$info = substr($info,0,($s-2));
//			$pattern = "0123456789";
//			for ($i=0; $i<10; $i++) {
//				$info = str_replace($pattern[$i],"",$info);
//			}
			$info = BH_util::depunctuate($info,"*");
			$info = str_replace("NY","",$info);			
			$info = str_replace("LA","",$info);						
			$info = explode(" vs ",$info);
			if (sizeof($info) != 2) continue;
			if (strpos($info[0],"*") !== false) {
				$winner = trim(str_replace("*","",$info[0]));
				$loser = trim($info[1]);
			} else if (strpos($info[1],"*") !== false) {
				$winner = trim(str_replace("*","",$info[1]));
				$loser = trim($info[0]);
			} else {
				continue;
			}

			if (substr(strtolower($winner),0,7) == "yankees") {
				$opponent = BH_util::prefix(" ",$loser);
				$won = true;
				$score = BH_util::suffix(" ",$winner)." to ".BH_util::suffix(" ",$loser);				
			} else if (substr(strtolower($loser),0,7) == "yankees") {
				$opponent = BH_util::prefix(" ",$winner);
				$won = false;
				$score = BH_util::suffix(" ",$loser)." to ".BH_util::suffix(" ",$winner);								
			} else {
				continue;
			}

			$feed->setVar("opponent",$opponent);
			$feed->setVar("won",($won ? "true" : "false"));
			$feed->setVar("when",$when);			
			$feed->setVar("score",$score);
			$updated = true;
			break;
		}
		
		if (!$updated) $feed->setVar("when","last season");			
		$feed->setVar("game",($updated ? "true" : "false"));
		if ($updated) $feed->setVar("last_update",strval(time()));		
		
	
	}

}

?>