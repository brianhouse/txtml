<?php
	require_once("header.php");
?>
		<div class="full">
<?php

$db = BH_db::open();

if (!empty($_POST['remove'])) {
	$db->call("DELETE FROM router WHERE keyword='".$db->safe($_POST['remove'])."'");
}

if (!empty($_POST['keyword']) && !empty($_POST['state'])) {
	$db->call("INSERT INTO router (keyword,state) VALUES ('".$db->safe(strtolower($_POST['keyword']))."','".$db->safe($_POST['state'])."')");
}

if (!empty($_POST['change_default']) && !empty($_POST['state'])) {
	$db->call("UPDATE default_state SET state='".$db->safe($_POST['state'])."'");
}

echo "\t\t\t<table cellpadding=\"5\" border=\"0\" width=\"680\">\n";
echo "\t\t\t\t<tr><td width=\"200\"><span class=\"index\">keyword</span></td><td width=\"240\"><span class=\"index\">state</span></td><td><span class=\"index\">add/remove</span></td></tr>\n";

$db->call("SELECT * FROM router ORDER BY state");
foreach ($db->getAll() as $map) {
	echo "\t\t\t\t<tr><td>".$map['keyword']."</td><td><a href=\"states.php?state=".urlencode($map['state'])."\">".$map['state']."</a></td><td><form name=\"remove_".$map['keyword']."\" method=\"post\"><input type=\"hidden\" name=\"remove\" value=\"".$map['keyword']."\" /></form>[ <a href=\"javascript:document.remove_".$map['keyword'].".submit();\">x</a> ]</td></tr>\n";
}

echo "\t\t\t\t<tr><form name=\"add\" method=\"post\">\n";
echo "\t\t\t\t\t<td><input type=\"text\" name=\"keyword\" size=\"15\" maxlength=\"15\" /></td>\n";
echo "\t\t\t\t\t<td>\n";
echo "\t\t\t\t\t\t<select name=\"state\">\n";
echo "\t\t\t\t\t\t\t<option name=\"\"></option>\n";
$db->call("SELECT name FROM states WHERE start=1 ORDER BY name");
$start_states = $db->getAll();
foreach ($start_states as $state) echo "\t\t\t\t\t\t\t<option name=\"".$state['name']."\">".$state['name']."</option>\n";
echo "\t\t\t\t\t\t</select>\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t\t<td>[ <a href=\"javascript:document.add.submit();\">+</a> ]</td>\n";
echo "\t\t\t\t</form></tr>\n";
echo "\t\t\t</table>\n";

echo "\t\t\t<hr />\n";

echo "\t\t\t<table cellpadding=\"5\" border=\"0\" width=\"680\">\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<form name=\"change_default\" method=\"post\">\n";
echo "\t\t\t\t\t<input type=\"hidden\" name=\"change_default\" value=\"true\">\n";
echo "\t\t\t\t\t<td width=\"200\"><span class=\"index\">default</span></td>\n";
$db->call("SELECT state FROM default_state LIMIT 1");
$name = $db->get("state");
if (empty($name)) {
	$name = "<i>empty</i>";
} else {
	$name = "<a href=\"states.php?state=$name\">$name</a>";
}
echo "\t\t\t\t\t<td width=\"240\">$name</td>\n";
echo "\t\t\t\t\t<td>\n";
echo "\t\t\t\t\t\t<select name=\"state\" onChange=\"document.change_default.submit();\">\n";
echo "\t\t\t\t\t\t\t<option name=\"\"></option>\n";
foreach ($start_states as $state) echo "\t\t\t\t\t\t\t<option name=\"".$state['name']."\">".$state['name']."</option>\n";
echo "\t\t\t\t\t\t</select>\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</form></tr>\n";

echo "\t\t\t</table>\n";

echo "\t\t</div>\n";
include("footer.php");

?>