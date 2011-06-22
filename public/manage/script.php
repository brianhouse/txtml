<?php

require_once("../../src/Interpreter.php");	

header("Content-type: text/plain");

if (!empty($_GET['address'])) {

	$db = BH_db::open();	
	$query = "SELECT sms.user_id,sms.time,sms.send,sms.content,sms.raw,user_vars.value as name FROM sms JOIN user_vars ON (sms.user_id = user_vars.user_id) WHERE address='".$db->safe($_GET['address'])."' AND var='name' ORDER BY time";
	$db->call($query);
	$messages = $db->getAll();
	if (!sizeof($messages)) {
		$query = "SELECT sms.user_id,sms.time,sms.send,sms.content,sms.raw FROM sms WHERE address='".$db->safe($_GET['address'])."' ORDER BY time";		
		$db->call($query);
		$messages = $db->getAll();
	}
	$current_id = 0;
	$current_day = null;
	$current_name = null;
	foreach ($messages as $message) {
		if (!$message['name']) $message['name'] = "PARTICIPANT";
		$name = $message['send'] ? "TINO" : strtoupper($message['name']);

		if ($message['user_id'] != $current_id) {
			if ($current_id != 0) {
				echo "/////////////////////////////////////////////////////////////////////////////////////////////////////////////////\n\n";
				$current_name = null;
			}
			$current_id = $message['user_id'];
		}
		$time = strtotime($message['time']);
		$day = date("l",$time);
		$time = date("g:ia",$time);

		if ($day !== $current_day) {
			echo "\n-".$day."-\n\n\n\n";
			$current_day = $day;
			$current_name = null;
		}
		
		if ($name !== $current_name) {
			echo "\n$name\n\n";
			$current_name = $name;
		}	
		
		echo "($time)\n";
		echo ($message['send'] ? $message['content'] : $message['raw'])."\n\n\n";
	}
	
}
	
?>
