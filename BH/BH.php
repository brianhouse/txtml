<?php

$package = substr(basename(__FILE__),0,2);
$dir = dirname(__FILE__);
//error_log("including package $package from $dir");
$dir = opendir($dir);
$files = array();
while ($file = readdir($dir)) {
	if (substr($file,0,3) != $package."_") continue;
	if (substr($file,-4) != ".php") continue;
	$files[] = $file;
}

sort($files);
foreach ($files as $file) require_once($file);

?>