<?php

class Feeder_time extends Feeder {

	public function update (Feed $feed) {
	
		$time = time();
		$hour = date("H",$time);
		if ($hour < 8) {		
			$daytime = "bright and early";
		} else if ($hour < 10) {
			$daytime = "morning";
		} else if ($hour < 12) {
			$daytime = "morning";
		} else if ($hour < 15) {
			$daytime = "afternoon";
		} else if ($hour < 17) {
			$daytime = "afternoon";
		} else if ($hour < 21) {
			$daytime = "evening";
		} else {
			$daytime = "night";
		}
		
		$weekday = date("l",$time);
		$month = date("F",$time);
		switch ($month) {
			case 'January': $season = "winter"; break;
			case 'February': $season = "winter"; break;
			case 'March': $season = "spring"; break;
			case 'April': $season = "spring"; break;
			case 'May': $season = "spring"; break;			
			case 'June': $season = "summer"; break;			
			case 'July': $season = "summer"; break;			
			case 'August': $season = "summer"; break;			
			case 'September': $season = "autumn"; break;						
			case 'October': $season = "autumn"; break;						
			case 'November': $season = "autumn"; break;						
			case 'December': $season = "winter"; break;									
		}
		$year = date("Y",$time);
		
		$feed->setVar("daytime",$daytime);
		$feed->setVar("weekday",$weekday);
		$feed->setVar("month",$month);
		$feed->setVar("season",$season);
		$feed->setVar("year",$year);		
		$feed->setVar("last_update",strval(time()));		
		
	}

}

?>