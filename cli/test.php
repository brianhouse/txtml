<?php

require_once(dirname(__FILE__)."/../src/Interpreter.php");

if (!isset($argv[1])) {
	echo "[text]\n";
	exit;
}

Interpreter::receive("000:test",$argv[1]);

?>