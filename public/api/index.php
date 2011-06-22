<?php

require_once("../../src/Interpreter.php");

header("Content-type: application/xml");
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?".">\n";

if (empty($_REQUEST['action'])) {
	echo "<result>NO ACTION SPECIFIED</result>\n";
	return;
}

if (empty($_REQUEST['user'])) {
	echo "<result>NO USER SPECIFIED</result>\n";
	return;
}

$module = BH_util::xmlencode($_REQUEST['action']);
$user = BH_util::xmlencode($_REQUEST['user']);
$input = empty($_REQUEST['input']) ? null : BH_util::xmlencode($_REQUEST['input']);
unset($_REQUEST['action']);
unset($_REQUEST['user']);
unset($_REQUEST['input']);

$parameters = array();
foreach ($_REQUEST as $key => $value) $parameters[$key] = $value;

Interpreter::api($module,$user,$input,$parameters);

echo "<result>OK</result>\n";

?>