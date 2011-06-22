<?php
	require_once("header.php");
?>
		<div class="menu">
			<span class="title">states</span><br /><br />	
<?php

$db = BH_db::open();

if (!empty($_POST['remove']) && !empty($_POST['verify']) && $_POST['verify'] = "on") {

	$state = urldecode($_POST['remove']);
	$db->call("DELETE FROM states WHERE name='".$db->safe($state)."'");
	$db->call("DELETE FROM blocks WHERE name='".$db->safe($state)."'");	

}

if (!empty($_POST['remove_collection']) && !empty($_POST['verify_collection']) && $_POST['verify_collection'] = "on") {

	$collection = urldecode($_POST['remove_collection']);
	$db->call("DELETE FROM states WHERE name LIKE '".$db->safe($collection).":%'");
	$db->call("DELETE FROM blocks WHERE name LIKE '".$db->safe($collection).":%'");

}

$collections = array();
$db->call("SELECT name FROM states ORDER BY name");
foreach ($db->getAll() as $state) {
	$collection = BH_util::prefix(":",$state['name']);
	$collections[$collection]['states'][] = $state['name'];
}
$db->call("SELECT name FROM blocks ORDER BY name");
foreach ($db->getAll() as $block) {
	$collection = BH_util::prefix(":",$block['name']);
	$collections[$collection]['blocks'][] = $block['name'];
}

foreach ($collections as $collection => $stuff) {
	echo "\t\t\t<span class=\"menu_category\"><br />".strtoupper($collection)."</span>\n";
	echo "\t\t\t<form method=\"post\" name=\"remove_$collection\"><input type=\"hidden\" name=\"remove_collection\" value=\"$collection\" /><input name=\"verify_collection\" type=\"checkbox\" /> <a href=\"javascript:document.remove_$collection.submit();\">remove all</a></form><br />\n";	
	if (isset($stuff['states'])) {
		foreach ($stuff['states'] as $state) {
			echo "\t\t\t\t<a class=\"menu_topic\" href=\"?state=".urlencode($state)."\">".BH_util::suffix(":",$state)."</a><br />\n";	
		}
	}
	if (isset($stuff['blocks'])) {
		foreach ($stuff['blocks'] as $block) {
			echo "\t\t\t\t<a class=\"menu_topic\" href=\"?block=".urlencode($block)."\">".BH_util::suffix(":",$block)."</a> (block)<br />\n";	
		}
	}
}

?>
		</div>
		<div class="content">
<?php

if (!empty($_GET['state'])) {

	if ($state = State::load($_GET['state'])) {
		echo "\t\t\t<span class=\"title\">".strtoupper($state->name())."</span><br />\n";
		echo "\t\t\tcreated ".BH_util::prefix("_",$state->filename())." (<a href=\"../txtml/".$state->filename()."\">file</a>)<br />\n";
		echo "\t\t\t<form method=\"post\" name=\"remove\"><input type=\"hidden\" name=\"remove\" value=\"".urlencode($state->name())."\" /><input name=\"verify\" type=\"checkbox\" /> <a href=\"javascript:document.remove.submit();\">remove</a></form><br /><br />\n";
		echo "\t\t\t<p class=\"code\">";
		$code = $state->display();
		$code = str_replace("\n","<br />",$code);
		$code = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$code);		
		echo $code;
		echo "\t\t\t</p>\n";
	}
}

if (!empty($_GET['block'])) {

	if ($block = Block::load($_GET['block'])) {
		echo "\t\t\t<span class=\"title\">".strtoupper($block->name())." (block)</span><br />\n";
		echo "\t\t\tcreated ".BH_util::prefix("_",$block->filename())." (<a href=\"../txtml/".$block->filename()."\">file</a>)<br />\n";
		echo "\t\t\t<form method=\"post\" name=\"remove\"><input type=\"hidden\" name=\"remove\" value=\"".urlencode($block->name())."\" /><input name=\"verify\" type=\"checkbox\" /> <a href=\"javascript:document.remove.submit();\">remove</a></form><br /><br />\n";
		echo "\t\t\t<p class=\"code\">";
		$code = $block->display();
		$code = str_replace("\n","<br />",$code);
		$code = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$code);		
		echo $code;
		echo "\t\t\t</p>\n";
	}
}

?>
		</div>
<?php
	include("footer.php");
?>