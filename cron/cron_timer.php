<?php

require_once(dirname(__FILE__)."/../src/Interpreter.php");

BH_util::log("TIMER CYCLE");

$max = BH_config::get("cron_cycle") ? BH_config::get("cron_cycle") : 1;
$max *= 60;
$max -= 5;

$time = 0;
BH_util::stopwatch("timer");		

while (true) {

	Interpreter::timer();
	
	sleep(60);

	$time += BH_util::stopwatch("timer");
	echo $time."\n";
	if ($time > $max) break;
	BH_util::stopwatch("timer");	
		
}

echo "\n".date("H:i:s")."\n";

?>