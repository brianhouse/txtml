<?php

header("Content-type: text/plain");

require_once("../../src/Interpreter.php");

if (!( 	!empty($_REQUEST['From']) &&	
		!empty($_REQUEST['Body']) &&
		!empty($_REQUEST['AccountSid'])				
	)) {

	echo "NEED From, Body, AccountSid";
	return;
}

$account = BH_config::get("twilio");

if ($_REQUEST['AccountSid'] != $account['sid']) {
	echo "Bad AccountSid";
	return;
}

$address = $_REQUEST['From'];
$address = BH_util::depunctuate($address);
if (substr($address, 0, 1) == "1") $address = substr($address, 1);
$content = $_REQUEST['Body'];

BH_util::log("==");
BH_util::log("TWILIO receive address [$address] content [$content]");	
BH_util::log("==");				

Interpreter::receive($address,$content);