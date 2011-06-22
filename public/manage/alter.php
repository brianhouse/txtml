<?php
	require_once("header.php");
?>
		<div class="full">
<?php

if (!empty($_POST)) {

	$data = http_build_query($_POST);
	$response = BH_net::scrape(BH_config::get("api")."?".$data);
	$response = trim(strip_tags((string)$response));
	if ($response == "OK") {
		echo "\t\t\t<a href=\"users.php\">OK</a>";
		unset($_GET['address']);		
	}

	
}


if (!empty($_GET['address'])) {

	echo "\t\t\t<span class=\"title\">ALTER USER ".$_GET['address']."</span><br /><br />\n";

	if (isset($response)) echo "\t\t\t<span class=\"alert\">$response</span><br /><br />\n";	

	echo "\t\t\t<span class=\"index\">set variable</span>\n";
	echo "\t\t\t<form name=\"set\" method=\"post\">\n";
	echo "\t\t\t\t<input name=\"action\" type=\"hidden\" value=\"set\" />\n";	
	echo "\t\t\t\t<input name=\"user\" type=\"hidden\" value=\"".$_GET['address']."\" />\n";	
	echo "\t\t\t\t<input name=\"var\" type=\"input\" value=\"var\" size=\"10\" />\n";
	echo "\t\t\t\t<input name=\"value\" type=\"input\" value=\"value\" size=\"10\" />\n";	
	echo "\t\t\t\t<a href=\"javascript:document.set.submit();\">set</a>\n";
	echo "\t\t\t</form><br />\n";

	echo "\t\t\t<span class=\"index\">unset variable</span>\n";
	echo "\t\t\t<form name=\"unset\" method=\"post\">\n";
	echo "\t\t\t\t<input name=\"action\" type=\"hidden\" value=\"unset\" />\n";	
	echo "\t\t\t\t<input name=\"user\" type=\"hidden\" value=\"".$_GET['address']."\" />\n";	
	echo "\t\t\t\t<input name=\"var\" type=\"input\" value=\"var\" size=\"10\" />\n";
	echo "\t\t\t\t<a href=\"javascript:document.unset.submit();\">unset</a>\n";
	echo "\t\t\t</form><br />\n";

	echo "\t\t\t<span class=\"index\">set state</span>\n";
	echo "\t\t\t<form name=\"send\" method=\"post\">\n";
	echo "\t\t\t\t<input name=\"action\" type=\"hidden\" value=\"send\" />\n";		
	echo "\t\t\t\t<input name=\"user\" type=\"hidden\" value=\"".$_GET['address']."\" />\n";		
	echo "\t\t\t\t<select name=\"state\">\n";
	$db = BH_db::open();
	$db->call("SELECT name FROM states");
	foreach ($db->getAll() as $state) echo "\t\t\t\t\t<option name=\"".$state['name']."\">".$state['name']."</option>\n";
	echo "\t\t\t\t\t\t</select>\n";
	echo "\t\t\t\t<a href=\"javascript:document.send.submit();\">set</a>\n";
	echo "\t\t\t</form><br />";

	echo "\t\t\t<span class=\"index\">remove</span>\n";
	echo "\t\t\t<form name=\"quit\" method=\"post\">\n";
	echo "\t\t\t\t<input name=\"action\" type=\"hidden\" value=\"quit\" />\n";	
	echo "\t\t\t\t<input name=\"silent\" type=\"hidden\" value=\"true\" />\n";	
	echo "\t\t\t\t<input name=\"user\" type=\"hidden\" value=\"".$_GET['address']."\" />\n";	
	echo "\t\t\t\t<a href=\"javascript:document.quit.submit();\">die</a>\n";
	echo "\t\t\t</form><br />\n";

}

echo "\t\t</div>\n";

include("footer.php");
	
?>
