<?php

class Requestor_ellis extends Requestor {

	protected function getData ($params) {
				
		$lastname = $params['lastname'];
		$firstname = $params['firstname'];
				
		// records from 1892-1924
		$target = "http://www.worldvitalrecords.com/zsearch.aspx?ix=ellisisland&qt=l&zln=$lastname&zfn=$firstname";
		BH_util::log("--> [requestor] get data [$target]");
		$page = BH_net::scrape($target,null,false,1);
		if (!$page) return null;
	
		$num_results = strip_tags($page);
		$num_results = BH_util::grab("<span class=\"results_info\">","</span>",$page);
		$num_results = trim(strip_tags($num_results));
		$num_results = intval(BH_util::depunctuate(BH_util::grab("of "," for",$num_results)));
	
		if ($num_results == 0) return null;
	
		$results = substr($page,strpos($page,"<!-- docid ",$page));
		$results = substr($results,0,strpos($results,"</table>",$results));
		$results = explode("docid",$results);
	
		foreach ($results as $result_key => $result) {
			$details = explode("<td>",$result);
			foreach ($details as $detail_key => $detail) {
				unset($details[$detail_key]);
				if ($detail_key == 0) continue;
				$detail = trim(strip_tags($detail));
				switch ($detail_key) {
					case 1: $attr = "firstname"; break;
					case 2: $attr = "lastname"; break;
					case 3: $attr = "birthyear"; break;
					case 4: $attr = "arrival"; break;
					case 5: $attr = "age"; break;				
					case 6: $attr = "residence"; break;								
				}
				$details[$attr] = $detail;
			}
			if (!sizeof($details) || empty($details['lastname'])) {
				unset($results[$result_key]);
				continue;
			}		
			$results[$result_key] = $details;		
		}
		$results = array_slice($results,0);

		if (!sizeof($results)) {
			BH_util::log("--> no results");
			return "Sorry, there's no record of that name.";	
		} else {
			BH_util::log("--> found results");
			if ($num_results == 1) {
				$string = "Just one listing: ";
			} else {
				$string = "Here's one of $num_results listings: ";
			}
			$index = rand(1,sizeof($results)) - 1;
			$string .= trim($results[$index]['firstname']." ".$results[$index]['lastname'])." ";
			$string .= "was here in ".BH_util::suffix(" ",$results[$index]['arrival']);
			$string .= empty($results[$index]['age']) ? "" : " at age ".$results[$index]['age'];
			$string .= ".";		
			return $string;
		}	
	
	}

}

?>