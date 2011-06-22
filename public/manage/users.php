<?php
	require_once("header.php");
?>
		<div class="full">
			<span class="title">users </span>
<?php

$db = BH_db::open();

$active = empty($_GET['active']) || $_GET['active'] == "true" ? true : false;
$active_string = $active ? "AND active=1" : "";
$search_string = empty($_GET['search']) ? "" : "AND name LIKE '%".($db->safe($_GET['search']))."%'";
$collection_prefix = empty($_GET['collection']) ? "" : "JOIN path ON (users.id = path.user_id)";
$collection_string = empty($_GET['collection']) ? "" : "AND state LIKE '".($db->safe($_GET['collection'])).":%'";

$db->call("SELECT count(DISTINCT users.id) AS count FROM users $collection_prefix WHERE 1=1 $collection_string $active_string $search_string");
$count = $db->get("count");
$pages = ceil($count / 20.0);

echo "\t\t\t<span class=\"index\">($count)</span><br />\n";

$page = !empty($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$page = $page > $pages ? $pages : $page;
$page = $page < 1 ? 1 : $page;

if ($pages > 1) {
	if ($page > 1) {
		echo "\t\t\t<a class=\"index\" href=\"?page=".($page-1).($active ? "" : "&active=false").(!empty($_GET['search']) ? "&search=".$_GET['search'] : "").(!empty($_GET['collection']) ? "&collection=".$_GET['collection'] : "")."\">&lt; </a>";
	} else {
		echo "\t\t\t&lt; ";
	}
	for ($i=1; $i<=$pages; $i++) {
		if ($i == $page) {
			echo "<span class=\"index\">$i</span> ";	
		} else {
			echo "<a class=\"index\" href=\"?page=$i".($active ? "" : "&active=false").(!empty($_GET['search']) ? "&search=".$_GET['search'] : "").(!empty($_GET['collection']) ? "&collection=".$_GET['collection'] : "")."\">$i</a> ";
		}
	}
	if ($page < $pages) {
		echo "<a class=\"index\" href=\"?page=".($page+1).($active ? "" : "&active=false").(!empty($_GET['search']) ? "&search=".$_GET['search'] : "").(!empty($_GET['collection']) ? "&collection=".$_GET['collection'] : "")."\"> &gt;</a>";
	} else {
		echo " &gt;";
	}
	echo "<br />\n";
}

if ($active) {
	unset($_GET['active']);
	$query = http_build_query($_GET);
	echo "\t\t\t<a href=\"?$query&active=false\">show inactive</a><br />\n";
} else {
	unset($_GET['active']);
	$query = http_build_query($_GET);
	echo "\t\t\t<a href=\"?$query\">hide inactive</a><br />\n";
}

$db->call("SELECT DISTINCT state FROM path");
$collections = array();
foreach ($db->getAll() as $collection) $collections[] = BH_util::prefix(":",$collection['state']);
$collections = array_flip($collections);
$collections = array_flip($collections);

echo "\t\t\t<form name=\"filter\" method=\"get\">";
echo "<input name=\"active\" type=\"hidden\" value=\"".($active ? "true" : "false")."\">";
echo "search <input name=\"search\" type=\"text\" size=\"15\" value=\"".(!empty($_GET['search']) ? $_GET['search'] : "")."\" /><br />";

echo "collection <select name=\"collection\" onChange=\"document.filter.submit()\">";
echo "<option name=\"\"></option>";
foreach ($collections as $collection) {
	if (!empty($_GET['collection']) && $_GET['collection'] == $collection) {
		echo "<option name=\"$collection\" selected=\"selected\">$collection</option>";
	} else {
		echo "<option name=\"$collection\">$collection</option>";	
	}
}
echo "</select><br />";

echo "<a href=\"?active=".($active ? "true" : "false")."\">clear</a>";
echo "</form>\n";



$start = ($page-1) * 20;

////

$db->call("SELECT DISTINCT(users.id),users.active,users.name FROM users $collection_prefix WHERE 1=1 $collection_string $active_string $search_string ORDER BY active DESC,last DESC,name LIMIT $start,20");

echo "\t\t\t<br /><br />\n";
echo "\t\t\t<table cellpadding=\"0\" border=\"0\" width=\"680\">\n";
echo "\t\t\t\t<tr><td><span class=\"index\">address</span></td><td><span class=\"index\">active</span></td><td><span class=\"index\">path</span></td><td><span class=\"index\">vars</span></td><td><span class=\"index\">actions</span></td></tr>\n";
foreach ($db->getAll() as $user) {
	if ($user['name'] == "api:api") continue;
	echo "\t\t\t\t<tr><td colspan=\"6\"><hr /></td></tr>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td width=\"100\">".implode(", ",explode(":",$user['name']))." (".$user['id'].")</td><td>".($user['active'] ? "YES" : "NO")."</td><td width=\"220\">";
	
	$db->call("SELECT * FROM path WHERE user_id={$user['id']} ORDER BY time, id");
	foreach ($db->getAll() as $node) {
		echo "<i>".substr($node['time'],5,-3)."</i> <a href=\"states.php?state=".urlencode($node['state'])."\">".$node['state']."</a><br />";
	}
	echo "</td>\n";
		
	echo "\t\t\t\t\t<td>";
	$db->call("SELECT * FROM user_vars WHERE user_id={$user['id']}");
	foreach ($db->getAll() as $var) echo $var['var']." = ".$var['value']."<br />";
	
	echo "</td>\n";

	echo "\t\t\t\t\t<td><a href=\"track.php?address=".urlencode($user['name'])."\">track</a><br /><a href=\"alter.php?address=".urlencode($user['name'])."\">alter</a><br /><a href=\"txt.php?address=".urlencode($user['name'])."\">txt</a><br /><a href=\"script.php?address=".urlencode($user['name'])."\">script</a></td>\n";
	echo "\t\t\t\t</tr>\n";
}
echo "\t\t\t</table>\n";

echo "\t\t</div>\n";
include("footer.php");

?>
