<?php

require_once(dirname(__FILE__)."/../src/Interpreter.php");

if (!isset($argv[1])) {
	echo "[pattern]\n";
	return;
}

$pattern = trim($argv[1]);

echo "> ";

while ($line = trim(fgets(STDIN))) {

$line = Language::clean($line);
echo "---> $line\n";
echo "---> ".(Language::match($line,$pattern) ? "true" : "false")."\n";
echo "> ";

}

?>