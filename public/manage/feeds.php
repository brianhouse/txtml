<?php
	require_once("header.php");
?>
		<div class="full">
			<span class="title">feeds</span><br />
<?php

$db = BH_db::open();

if (!empty($_POST['remove'])) {
	$feed = Object::load("Feed",strtolower($_POST['remove']));	
	$feed->remove();
}

if (!empty($_POST['name'])) {
	Object::create("Feed",strtolower($_POST['name']));
}

if (!empty($_POST['update'])) {
	Feed::updateAll();
}

echo "\t\t\t<table cellpadding=\"5\" border=\"0\" width=\"680\">\n";
echo "\t\t\t\t<tr><td><span class=\"index\">name</span></td><td><span class=\"index\">vars</span></td><td><span class=\"index\">clear</span></td></tr>\n";

$db->call("SELECT * FROM feeds");
foreach ($db->getAll() as $feed) {
	echo "\t\t\t\t<tr><td><a href=\"docs.php?category=feeds&amp;topic=".$feed['name']."\">".$feed['name']."</a></td>";
	echo "<td>";
	$db->call("SELECT * FROM feed_vars WHERE feed_id={$feed['id']}");
	foreach ($db->getAll() as $row) {
		if ($row['var'] == "last_update") {
			echo "(".date("Y-m-d @ H:i",$row['value']).")<br />";
		} else {
			echo $row['var']." = ".$row['value']."<br />";
		}
	}
	echo "</td>\n";
	echo "<td><form name=\"remove_".$feed['name']."\" method=\"post\"><input type=\"hidden\" name=\"remove\" value=\"".$feed['name']."\" /></form>[ <a href=\"javascript:document.remove_".$feed['name'].".submit();\">x</a> ]</td></tr>\n";
}

echo "\t\t\t\t<tr><form name=\"update\" value=\"update\" method=\"post\">\n";
echo "\t\t\t\t\t<td colspan=\"3\"><input name=\"update\" type=\"hidden\" value=\"1\" /><a href=\"javascript:document.update.submit();\">update all feeds now</a></td>\n";
echo "\t\t\t\t</form></tr>\n";

echo "\t\t\t</table>\n";
echo "\t\t</div>\n";
include("footer.php");

?>