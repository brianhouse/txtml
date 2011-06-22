<?php
	require_once("header.php");

$directory = BH_config::basedir()."/docs/";
if ( !($handle = opendir($directory))) {
	BH_util::log("docs directory is missing");
	return null;
}

$files = array();
while ($folder = readdir($handle)) {
	if (substr($folder,0,1) == ".") continue;
	if (!is_dir($directory.$folder)) continue;		
	if (!$h = @opendir($directory.$folder)) continue;	
	while ($file = readdir($h)) {
		if (substr($file,0,1) == ".") continue;
		if (is_dir($directory.$file)) continue;		
		$files[$folder][] = $file;
	}	
}	
ksort($files);
foreach ($files as $key => $folder) {
	ksort($folder);
	$files[$key] = $folder;
}

$categories = array();
foreach ($files as $folder => $files) {
	$categories[BH_util::suffix("_",$folder)] = array();
	foreach ($files as $file) {
		$categories[BH_util::suffix("_",$folder)][BH_util::suffix("_",BH_util::prefix(".",$file))] = $directory.$folder."/".$file;
	}
}

?>
		<div class="menu">
			<span class="title">TXTML DOCS</span><br /><br />
<?php

foreach ($categories as $category => $topics) {
	echo "\t\t\t<span class=\"menu_category\"><br />".strtoupper($category)."</span><br />\n";	
	foreach ($topics as $topic => $path) {
		if (substr($topic,0,5) == "link") {
			$link = file($path);			
			echo "\t\t\t\t- <a class=\"menu_topic\" href=\"".trim($link[1])."\">".trim($link[0])."</a><br />\n";
		} else {
			echo "\t\t\t\t- <a class=\"menu_topic\" href=\"?category=$category&amp;topic=$topic\">$topic</a><br />\n";
		}
	}
}

?>
		</div>
		
			
		<div class="content">
<?php

$category = isset($_GET['category']) ? $_GET['category'] : null;
$topic = isset($_GET['topic']) ? $_GET['topic'] : null;

if (isset($categories[$category][$topic])) {
	$content = file_get_contents($categories[$category][$topic]);
	$content = trim($content);
	$content = nl2br($content);	
	$content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$content);
	$content = str_replace("{","&lt;",$content);
	$content = str_replace("}","&gt;",$content);
	$content = str_replace("PARAMETERS","<span class=\"menu_category\">PARAMETERS</span>",$content);		
	$content = str_replace("EXAMPLES","<span class=\"menu_category\">EXAMPLES</span>",$content);			
	$content = str_replace("ATTRIBUTES","<span class=\"menu_category\">ATTRIBUTES</span>",$content);				
	$content = str_replace("DESCRIPTION","<span class=\"menu_category\">DESCRIPTION</span>",$content);				
	$content = str_replace("PATTERN - NATURAL LANGUAGE PROCESSING","<span class=\"menu_category\">PATTERN - NATURAL LANGUAGE PROCESSING</span>",$content);						
	$content = str_replace("[","<div class=\"code\">",$content);	
	$content = str_replace("]","</div>",$content);		
	echo "\t\t\t<span class=\"title\">$category : $topic</span><br /><br /><br />\n";
	echo "\t\t\t<div class=\"topic\">$content</div>\n";
}

?>
		</div>
		
<?php
	include("footer.php");
?>