<?php

class Feeder_tidesnyc extends Feeder {

	public function update (Feed $feed) {

		return; // disabled
		
		// assumptions:
		// readings are taken every 6 minutes
		// this feeder is updated every hour
		// the period of a tide is 12 hours
		// predicted readings reach into the future 12 hours
		
		// indexes of data page
		define("DATE",0);
		define("TIME",1);
		define("PREDICTED",3);
		define("ACTUAL",4);
		
		// get page
		$target = "http://tidesonline.noaa.gov/data_read.shtml?station_info=8518750+The+Battery,+NY";
		$page = BH_net::scrape($target);
		if (!$page) return;
		$page = strip_tags($page);
		$page = BH_util::singlespace($page);
		$page = BH_util::linenormalize($page);
		$lines = explode("\n",$page);
		
		// separate data
		$data = array();
		foreach ($lines as $line) {
			$point = array();
			$line = explode(" ",trim($line));
			if (!isset($line[DATE]) || !isset($line[TIME]) || !isset($line[PREDICTED]) || !is_numeric($line[PREDICTED])) continue;	
			$point['time'] = strtotime($line[DATE]." ".$line[TIME]);
			$point['level'] = $line[PREDICTED] == "-99.99" ? null : $line[PREDICTED];	// no point in using actual readings
			$data[] = $point;
		}
		
		// find max and min for dataset
		$min = 100;
		$max = 0;
		foreach ($data as $d) {
			if ($d['level'] > $max) $max = $d['level'];
			if ($d['level'] < $min) $min = $d['level'];	
		}
		
		// find closest reading
		$time = time();
		$current = 0;
		foreach ($data as $key => $d) {
			if (abs($d['time'] - $time) < abs($data[$current]['time'] - $time)) $current = $key;
		}
		
		// average the next hour of data
		$average = 0;
		for ($i=$current; $i<$current+10; $i++) $average += $data[$i]['level'];
		$average /= 10;
		
		// average the previous hour of data
		$previous = 0;
		for ($i=$current-10; $i<$current; $i++) $previous += $data[$i]['level'];
		$previous /= 10;
		
		// determine tide status
		$step = ($max - $min) / 4;
		if ($average < $min + $step) {
			$tide = "low";
		} else if ($average < $min + (3 * $step)) {
			$tide = $average < $previous ? "ebbing" : "rising";
		} else {
			$tide = "high";
		}

		$feed->setVar("tide",$tide);
		$feed->setVar("last_update",strval(time()));		

	}

}

?>