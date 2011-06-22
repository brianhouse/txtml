<?php
	require_once("header.php");
?>
		<div class="full">
<?php

if (!empty($_POST)) {

	$data = http_build_query($_POST);
	$response = BH_net::scrape(BH_config::get("api")."?".$data);
	echo "[".$response."]<br />\n";
	echo "\t\t\t<a href=\"users.php\">OK</a>";
	unset($_GET['address']);
	
}


if (!empty($_GET['address'])) {

	echo "\t\t\t<span class=\"title\">TXT USER ".$_GET['address']."</span><br /><br />\n";
	
	echo "\t\t\t<form name=\"txt_user\" method=\"post\">\n";
	echo "\t\t\t\t<input name=\"action\" type=\"hidden\" value=\"txt\" />\n";	
	echo "\t\t\t\t<input name=\"user\" type=\"hidden\" size=\"30\" maxlength=\"100\" value=\"".(!empty($_GET['address']) ? $_GET['address'] : "")."\" /><br /><br />\n";	
	echo "\t\t\t\t<span class=\"index\">content</span><br />\n";
	echo "\t\t\t\t<input name=\"string\" type=\"input\" size=\"130\" maxlength=\"160\" value=\"\" /><br /><br />\n";	
	echo "\t\t\t\t<a href=\"javascript:document.txt_user.submit();\">txt</a>\n";
	echo "\t\t\t</form><br />\n";

}

echo "\t\t</div>\n";

include("footer.php");
	
?>
