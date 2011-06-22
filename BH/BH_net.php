<?php

class BH_net {


	static function scrape ($target) {

		static $socket = null;
			
		// grab params
		$args = func_get_args();
		$data = !empty($args[1]) ? $args[1] : null;
		$proxy = !empty($args[2]) ? true : false;		
		$timeout = !empty($args[3]) ? $args[3] : 30;
		
		// format target
		$url = parse_url($target);
		if (!isset($url['scheme'])) $url['scheme'] = "http";
		if (!isset($url['port'])) $url['port'] = 80;
		if (!isset($url['path'])) $url['path'] = "/";
		$url['type'] = $data ? "POST" : "GET";

		BH_util::log("BH: scraping [".$url['host']."]");

		if ( !$socket = @fsockopen($url['host'],$url['port'],$errno,$errstr,$timeout) ) {  // timeout is only for connecting
			BH_util::log("BH: connection error [$errstr ($errno)]");
			return null;
		}
		
		// write header and data
		$out = "";
		$out .= $url['type']." ".$url['path'];
		if (isset($url['query'])) $out .= "?".$url['query'];
		$out .= " HTTP/1.0\r\n";
		$out .= "Host: ".$url['host']."\r\n";			
		if ($data) {
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Content-length: ".strlen($data)."\r\n";
		}
		$out .= "Connection: Close\r\n";							
		$out .= "\r\n";
		if ($data) $out .= $data."\r\n";
		$out .= "\r\n";
				
		if (!fwrite($socket,$out)) {
			BH_util::log("BH: unable to send data");	
			return null;
		}

		$code = explode(" ",fgets($socket,1024));
		$code = $code[1];
		
		if (!$proxy) {		
			// auto-skip header
			$started = false;
			$headerline = false;
			while ( !feof($socket) && ($started == false || $headerline == true)) {		
				if ( $headerline = trim(fgets($socket,1024)) != "" ? true : false) $started = true;			
			}
		} else {			
			// repeat headers
			$started = false;
			$header = false;
			while ( !feof($socket) && ($started == false || $header == true)) {		
				$line = trim(fgets($socket,1024));
				if ($header = $line != "" ? true : false) $started = true;			
				if ($started && $header) header($line);
			}
		}

		// buffer output
		$page = "";
		while (!feof($socket)) {
			$line = fgets($socket,1024);
			if ($line == "") continue;
			$page .= $line;
		}
		
		// clean up
		fclose($socket);
		$socket = null;

		if (substr($code,0,1) != "2") {  // return false if not 200
			BH_util::log("BH: unsuccessful query ($code) [$page]");
			return null;
		}
		
		BH_util::log("BH: successful query ($code)");		
		if ($proxy) {
			echo $page;
		} else {
			return $page;
		}
	
	}

	static function rest ($url,$method="GET",$data=null) {
		
		if ($method != "GET" && $method != "POST" && $method != "PUT" && $method != "DELETE") {
			BH_util::log("BH: rest requires GET POST PUT or DELETE");
			return;
		}
		
		$sock = curl_init(); 
		$options = array(	CURLOPT_URL => $url,
							CURLOPT_HEADER => false,
							CURLOPT_CUSTOMREQUEST => $method,
							CURLOPT_RETURNTRANSFER => true
		                );
		if ($data) $options[CURLOPT_POSTFIELDS] = $data;
		// Data accepts either arrays or 'para1=val1&para2=val2&...'
		// To post a file, prepend a filename with @ and use the full path.
		
		curl_setopt_array($sock, $options);
		if (curl_errno($sock)) {
			BH_util::log("BH: ".curl_error($sock));
			return null;
		}
		$output = curl_exec($sock);
		$status = curl_getinfo($sock,CURLINFO_HTTP_CODE);		
		$result = array("status" => $status, "response" => $output);
		curl_close($sock);
		return $result;		
		
	}

}

?>