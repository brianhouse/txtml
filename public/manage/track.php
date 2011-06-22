<?php
	require_once("header.php");
?>
		<div class="full">
<?php

if (!empty($_GET['address'])) {

	echo "\t\t\t<span class=\"title\">TRACKING ".$_GET['address']."</span><br /><br />\n";

	$db = BH_db::open();	
	$db->call("SELECT * FROM sms WHERE address='".$db->safe($_GET['address'])."' ORDER BY time");
	echo "\t\t\t<table>\n";
	echo "\t\t\t\t<tr><td><span class=\"index\">time</span></td><td><span class=\"index\">state</span></td><td><span class=\"index\">direction</span></td><td><span class=\"index\">content</span></td></tr>\n";
	$current_id = 0;
	foreach ($db->getAll() as $message) {
		if ($message['user_id'] != $current_id) {
			if ($current_id != 0) echo "\t\t\t\t<tr><td colspan=\"4\"><hr /></td></tr>\n";
			$current_id = $message['user_id'];
		}
		echo "\t\t\t\t<tr><td align=\"left\" valign=\"top\">".$message['time']."</td><td align=\"left\" valign=\"top\"><a href=\"states.php?state=".urlencode($message['state'])."\">".$message['state']."</a></td><td align=\"left\" valign=\"top\">".($message['send'] == "1" ? "MT" : "MO")."</td><td align=\"left\" valign=\"top\" width=\"60%\">".$message['content']."</td></tr>\n";
	}
	echo "\t\t\t</table>\n";
	echo "\t\t</div>\n";
	
}

	include("footer.php");
	
?>
