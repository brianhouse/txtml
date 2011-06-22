<?php

class Module_time extends Module {

	protected function x ($user,$time,$input) {
		
		$time = time(); //
		$now = array();
		$triggers = array();
						
		foreach ($this->params() as $key => $param) {
		
			// meta parameters
			if ($key == "repeat" || $key == "delay" || $key == "stopwatch" || $key == "name") {
				$p = explode(",",$param);
				$triggers[$key] = $p[0];
				continue;
			}
			
			// wildcards
			if (strpos($param,"*") !== false) {
				$triggers[$key] = array( "*" );
				continue;
			}
			
			// operational parameters
			$triggers[$key] = array();
			$param = explode(",",$param);			
			foreach ($param as $p) {
				if ($key == "year") {
					$p = (int)$p;
					if ($p > 2000) $p -= 2000;
					$triggers[$key][] = $p;
				} else if ($key == "day") {
					$triggers[$key][] = strtoupper(substr($p,0,3));
				} else {
					$triggers[$key][] = (int)$p;			
				}
			}
		}
		
		if (!isset($triggers['repeat'])) $triggers['repeat'] = "false";				
		if (!isset($triggers['delay'])) $triggers['delay'] = "0";
		if (!isset($triggers['stopwatch'])) $triggers['stopwatch'] = "0";		
		if (!isset($triggers['year'])) $triggers['year'][0] = "*";
		if (!isset($triggers['month'])) $triggers['month'][0] = "*";
		if (!isset($triggers['date'])) $triggers['date'][0] = "*";
		if (!isset($triggers['day'])) $triggers['day'][0] = "*";		
		if (!isset($triggers['hour'])) $triggers['hour'][0] = "*";		
		if (!isset($triggers['minute'])) $triggers['minute'][0] = "*";				

		$now['repeat'] = $triggers['repeat'] == "false" && ( $user->getVar("trig_".$triggers['name']) == "true" || $user->getVar("swtrig_".$triggers['name']) == "true" ) ? "false" : "true";
		$now['delay'] = (int)floor(($time - strtotime($user->last())) / 60.0);
		
		$gtime = $user->getVar("stopwatch") ? $user->getVar("stopwatch") : 0;
		$now['stopwatch'] = $gtime == 0 ? 0 : (int)floor(($time - strtotime($gtime)) / 60.0);
		
		$now['year'] = (int)date("y",$time);
		$now['month'] = (int)date("n",$time);
		$now['date'] = (int)date("j",$time);
		$now['day'] = strtoupper(substr(date("D",$time),0,3));
		$now['hour'] = (int)date("G",$time);
		$now['minute'] = (int)date("i",$time);

		$log = "time triggers";
		foreach ($triggers as $key => $trigger) {
			if ($key == "name") continue;
			$log .= " $key [".$now[$key]."|";
			$l = "";
			if (is_array($trigger)) {
				foreach ($trigger as $t) $l .= "$t,";
			} else {
				$l .= $trigger;
			}
			$log .= trim($l,",")."]";		
		}

		$delay_match = $now['delay'] >= $triggers['delay'] ? true : false;
		$stopwatch_match = $now['stopwatch'] >= $triggers['stopwatch'] ? true : false;
		$repeat_match = $now['repeat'] == "true" ? true : false;
		$year_match	= in_array($now['year'],$triggers['year']) || $triggers['year'][0] == "*" ? true : false;
		$month_match = in_array($now['month'],$triggers['month']) || $triggers['month'][0] == "*" ? true : false;		
		$date_match = in_array($now['date'],$triggers['date']) || $triggers['date'][0] == "*" ? true : false;				
		$day_match = in_array($now['day'],$triggers['day']) || $triggers['day'][0] == "*" ? true : false;				
		$hour_match = in_array($now['hour'],$triggers['hour']) || $triggers['hour'][0] == "*" ? true : false;				
		$minute_match = in_array($now['minute'],$triggers['minute']) || $triggers['minute'][0] == "*" ? true : false;						

		if ($delay_match && $stopwatch_match && $repeat_match && $year_match && $month_match && $date_match && $day_match && $hour_match && $minute_match) {
			$match = true;
			BH_util::log("$log -> match!",true);
			if ($triggers['stopwatch']) {
				$user->setVar("swtrig_".$triggers['name'],"true");
			} else {
				$user->setVar("trig_".$triggers['name'],"true");			
			}
		} else {
			$match = false;			
			BH_util::log("$log -> no match");		
		}
		
		if ($match) {
			$return = "";
			foreach ($this->submods as $submod) {
				$r = $submod->execute($user,$time,$input);
				if ($r === false) return false;
				if ($r !== true) $return .= $r;
			}
			return $return ? $return : true;
		} else {
			return true;
		}
			
	}
	
}

?>