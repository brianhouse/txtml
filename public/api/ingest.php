<?php

require_once("../../src/Interpreter.php");

BH_util::log("receiving ingestion");

function libxml_display_errors () {

	$errors = libxml_get_errors();
	foreach ($errors as $error) {
		echo "\t<error>".BH_util::zeropad($error->line,3)." ".trim($error->message)."</error>\n";
	}
	libxml_clear_errors();
	echo "</result>";
	exit;

}

header("Content-type: application/xml");
echo "<result>\n";

if (empty($_FILES['txtml'])) {
	echo "\t<error>NEED TXTML FILE</error>\n";
	echo "</result>";
	exit;
}

if ( $_FILES['txtml']['size'] > 100000 ) {
	echo "\t<error>File cannot be larger than 100KB.</error>\n";
	echo "</result>";	
	exit;
}

if ( $_FILES['txtml']['type'] != "text/xml" && $_FILES['txtml']['type'] != "application/xml" && $_REQUEST['type'] != "text/xml" && $_REQUEST['type'] != "application/xml" ) {
	echo "\t<error>File is [".$_FILES['txtml']['type']."] but must be [text/xml] or [application/xml].</error>\n";
	echo "</result>";	
	exit;
}

$xml_string = file_get_contents($_FILES['txtml']['tmp_name']);  

if (!$xml = @simplexml_load_string($xml_string)) {
	echo "\t<error>NOT VALID XML</error>\n";
	echo "</result>";
	exit;
}

libxml_use_internal_errors(true);
$validator = new DOMDocument();
$validator->loadXML($xml_string);
if (!$valid = @$validator->schemaValidate("../txtml.xsd")) {
	libxml_display_errors();
} else if ($bad_char = gsmValidate(strip_tags($xml_string))) {
	echo "\t<error>BAD CHARACTER ($bad_char)</error>\n";
	echo "</result>";
	exit;
}
libxml_use_internal_errors(false);

$filename = tempnam(BH_config::basedir()."/public/txtml/",date("Y-m-d")."_");
file_put_contents($filename,$xml_string);
chmod($filename,0666);		
$filename = BH_util::suffix("/",$filename);
$collection = (string)$xml['collection'];
	
// kill comments			
function killComments ($xml) {			
	unset($xml->comment);
	foreach ($xml as $key => $child) killComments($child);
}	
killComments($xml);					

foreach ($xml->state as $state) {

	if (empty($state['name'])) continue;

	$name = strpos($state['name'],":") === false ? $collection.":".$state['name'] : "".$state['name'];
	$start = isset($state['start']) && strtolower($state['start']) == "true" ? 1 : 0;

	unset($state['name']);
	unset($state['start']);

	if (empty($state['quit']) && !empty($xml['quit'])) $state['quit'] = (string)$xml['quit'];
	if (empty($state['help']) && !empty($xml['help'])) $state['help'] = (string)$xml['help'];
	if (empty($state['timeout']) && !empty($xml['timeout'])) $state['timeout'] = (string)$xml['timeout'];
	if (empty($state['prefix']) && !empty($xml['prefix'])) $state['prefix'] = (string)$xml['prefix'];

	$data = $state->asXML();
	
	// prefix all states and blocks with collection
	$data = prefix($collection,"state",$data);
	$data = prefix($collection,"block",$data);				
	
	$db = BH_db::open();
	$db->call("SELECT name FROM states WHERE name='$name'");
	if ($db->rows()) {
		if ($db->call("UPDATE states SET start=$start,txtml='".$db->safe($data)."',filename='".$db->safe($filename)."' WHERE name='$name'")) {
			echo "\t<update>$name</update>\n";
			BH_util::log("successfully updated state [$name]");	
		} else {
			echo "\t<failed>$name</failed>\n";
			BH_util::log("update state failed [$name]");		
		}			
	} else {
		if ($db->call("INSERT INTO states (name,start,txtml,filename) VALUES ('".$db->safe($name)."',$start,'".$db->safe($data)."','".$db->safe($filename)."')")) {
			echo "\t<new>$name</new>\n";
			BH_util::log("successfully insert state [$name]");	
		} else {
			echo "\t<failed>$name</failed>\n";
			BH_util::log("insert state failed [$name]");		
		}			
	}
		
}

function loadBlocks ($xml,$collection,$filename) {			
	foreach ($xml->block as $key => $block) {
	
		if (empty($block['name'])) continue;
		$name = strpos($block['name'],":") === false ? $collection.":".$block['name'] : "".$block['name'];
		unset($block['name']);

		$data = $block->asXML();

		// prefix all states and blocks with collection
		$data = prefix($collection,"state",$data);
		$data = prefix($collection,"block",$data);				
		
		$db = BH_db::open();
		$db->call("SELECT name FROM blocks WHERE name='$name'");
		if ($db->rows()) {
			if ($db->call("UPDATE blocks SET txtml='".$db->safe($data)."',filename='".$db->safe($filename)."' WHERE name='$name'")) {
				echo "\t<update>$name</update>\n";
				BH_util::log("successfully updated block [$name]");	
			} else {
				echo "\t<failed>$name</failed>\n";
				BH_util::log("update block failed [$name]");		
			}			
		} else {
			if ($db->call("INSERT INTO blocks (name,txtml,filename) VALUES ('".$db->safe($name)."','".$db->safe($data)."','".$db->safe($filename)."')")) {
				echo "\t<new>$name</new>\n";
				BH_util::log("successfully insert block [$name]");	
			} else {
				echo "\t<failed>$name</failed>\n";
				BH_util::log("insert block failed [$name]");		
			}			
		}

	}
	foreach ($xml as $key => $child) loadBlocks($child,$collection,$filename);
}	
loadBlocks($xml,$collection,$filename);	
echo "</result>\n";
		
function prefix ($collection,$attribute,$data) {

	$pattern = '/'.$attribute.'="[^:"]+"/';
	preg_match_all($pattern,$data,$matches);
	foreach ($matches[0] as $match) {
		$data = str_replace($match,substr($match,0,7).$collection.":".substr($match,7),$data);
	}			
	return $data;

}

function gsmValidate ($string) {

	$string = BH_util::detab($string);
	$pattern = "0123456789";
	$pattern .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$pattern .= "abcdefghijklmnopqrstuvwxyz";
	$pattern .= "@_!#%*+,-.:;=?$)(/ '";
	for ($i=0; $i<strlen($string); $i++) {
		if (strpos($pattern,substr($string,$i,1)) === false) {
			return substr($string,$i,1);
		}		
	}
	return null;

}	

	
?>