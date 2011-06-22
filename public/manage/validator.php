<?php

require_once("../../src/Interpreter.php");


function libxml_display_errors () {

	$errors = libxml_get_errors();
	foreach ($errors as $error) {
		echo "Line ".BH_util::zeropad($error->line,3).": <span class=\"alert\">".trim($error->message)."</span><br />";
	}
	libxml_clear_errors();

}

if (!empty($_FILES['add'])) {

	if ( $_FILES['add']['size'] > 100000 ) {
		echo "File cannot be larger than 100KB.";
		exit;
	}

	if ( $_FILES['add']['type'] != "text/xml" && $_FILES['add']['type'] != "application/xml" ) {
		echo "File is [".$_FILES['add']['type']."] but must be [text/xml] or [application/xml].";
		exit;
	}

	$xml_string = file_get_contents($_FILES['add']['tmp_name']);  
	
	if (!$xml = @simplexml_load_string($xml_string)) {
		header("Content-type: application/xml");
		echo $xml_string;
		exit;
	}

	require_once("header.php");
	echo "\t\t<div class=\"full\"/>\n";

	libxml_use_internal_errors(true);
	$validator = new DOMDocument();
	$validator->loadXML($xml_string);
	echo "<span class=\"index\">Validator results</span><br />\n";
	if (!$valid = @$validator->schemaValidate("../txtml.xsd")) {
		echo "<br />\n";
		libxml_display_errors();
	} else if ($bad_char = gsmValidate(strip_tags($xml_string))) {	
		echo "<br />\n";	
		echo "<span class=\"alert\">The character </span>".$bad_char."<span class=\"alert\"> is not allowed</span>";	
		$valid = false;
	} else {
		echo "Success";
	}
	echo "\n<br /><hr />\n";
	libxml_use_internal_errors(false);

	if (!empty($_REQUEST['load']) && $valid)  {

		BH_util::log("received file [".$_FILES['add']['name']."]");
		
		$filename = tempnam(BH_config::basedir()."/public/txtml/",date("Y-m-d")."_");
		file_put_contents($filename,$xml_string);
		chmod($filename,0666);		
		$filename = BH_util::suffix("/",$filename);
		
		echo "<span class=\"index\">States loaded</span><br />\n";
	
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
					echo "<a href=\"states.php?state=$name\">$name</a> (update)<br />";
					BH_util::log("successfully updated state [$name]");	
				} else {
					echo "$name failed! (update)<br />";
					BH_util::log("update state failed [$name]");		
				}			
			} else {
				if ($db->call("INSERT INTO states (name,start,txtml,filename) VALUES ('".$db->safe($name)."',$start,'".$db->safe($data)."','".$db->safe($filename)."')")) {
					echo "<a href=\"states.php?state=$name\">$name</a> (new)<br />";
					BH_util::log("successfully insert state [$name]");	
				} else {
					echo "$name failed! (new)<br />";
					BH_util::log("insert state failed [$name]");		
				}			
			}
				
		}
		
		echo "<br /><span class=\"index\">Blocks loaded</span><br />\n";		
		
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
						echo "<a href=\"states.php?block=$name\">$name</a> (update)<br />";
						BH_util::log("successfully updated block [$name]");	
					} else {
						echo "$name failed! (update)<br />";
						BH_util::log("update block failed [$name]");		
					}			
				} else {
					if ($db->call("INSERT INTO blocks (name,txtml,filename) VALUES ('".$db->safe($name)."','".$db->safe($data)."','".$db->safe($filename)."')")) {
						echo "<a href=\"states.php?block=$name\">$name</a> (new)<br />";
						BH_util::log("successfully insert block [$name]");	
					} else {
						echo "$name failed! (new)<br />";
						BH_util::log("insert block failed [$name]");		
					}			
				}

			}
			foreach ($xml as $key => $child) loadBlocks($child,$collection,$filename);
		}	
		loadBlocks($xml,$collection,$filename);	
				
		echo "\n<hr />\n";
	
	}

} else {

	require_once("header.php");
	echo "\t\t<div class=\"full\"/>\n";	

}

?>
			<form action="validator.php" method="post" enctype="multipart/form-data">
				<input type="file" name="add" />
				<input type="submit" value="Validate Only" />
				<input type="submit" name="load" value="Validate and Load states" />
			</form>
		</div>
<?php
	include("footer.php");
	
	
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