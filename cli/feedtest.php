<?php

require_once(dirname(__FILE__)."/../src/Interpreter.php");

if (!isset($argv[1])) {
	echo "[feed]\n";
	return;
}

$name = trim($argv[1]);

$classes = get_declared_classes();
foreach ($classes as $class) {
	if ($class != "Feeder_".$name) continue;
	$feed = Object::create("Feed",$name);			
	$feed->update();			
	$feed->destroy();
}

?>