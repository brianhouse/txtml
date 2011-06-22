<?php

class BH_util {
		
	
	static function log ($message) {
	
		static $on = true;
		if (func_num_args() > 1) {
			$switch = func_get_arg(1) ? true : false;
			if ($switch != $on) error_log("----- turning logging ".($switch ? "on" : "off")."\n",3,BH_config::basedir()."/log.log");
			$on = $switch;
		}
		if (!$on) return;
	
		date_default_timezone_set('America/New_York');	
		$time = date("Y-m-d H:i:s");
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "cli";
		error_log("$time [$ip] $message\n",3,BH_config::basedir()."/log.log");	
	
	}


	static function stopwatch ($id) {
	
		static $times = array();
		if (!isset($times[$id])) {
			$times[$id] = microtime(true);	
			return;
		} else {
			$duration = microtime(true) - $times[$id];
			unset($times[$id]);
			return $duration;
		}

	}


	static function xmlencode ($text) {
	
		$text = str_replace("&","&amp;",$text);
		$text = str_replace("<","&lt;",$text);
		$text = str_replace(">","&gt;",$text);
		$text = str_replace('"',"&quot;",$text);
		$text = str_replace("'","&apos;",$text);		
		return $text;
			
	}	


	static function xmldecode ($text) {
	
		$text = str_replace("&amp;","&",$text);
		$text = str_replace("&lt;","<",$text);
		$text = str_replace("&gt;",">",$text);
		$text = str_replace("&quot;",'"',$text);
		$text = str_replace("&apos;","'",$text);		
		return $text;
			
	}	

	
	static function validateEmail ($address) {
		
		$pattern = '/^([-a-zA-Z0-9_.]+)@(([-a-zA-Z0-9_]+[.])+[a-zA-Z]+)$/i' ;
		if (preg_match($pattern,$address)) {
			return true ;
		} else {
			return false ;
		}
		
	}
	
	
	static function validateSMSEmail ($address) {
	
		$mailbox = explode("@",$address);
		$mailbox = $mailbox[0];
		return BH_util::validateEmail($address) && preg_match("/[0-9]{10}/",$mailbox) ? true : false;
	
	}


	static function processPhotoString ($string,$newpath,$target_width,$target_height) {

		$img = imagecreatefromstring($string);
		if (!$img) return false;
		$img_new = BH_util::resizePhoto($img,$target_width,$target_height);
		imagejpeg($img_new,$newpath,100);		
		return true;

	}


	static function processPhoto ($imagefile,$newpath,$target_width,$target_height) {

		$name = $imagefile['name'];
		$tempname = $imagefile['tmp_name'];
		if ($imagefile['size'] > 3000000 || $imagefile['type'] != "image/jpeg") return false;
		$img = imagecreatefromjpeg($tempname);
		if (!$img) return false;
		$img_new = BH_util::resizePhoto($img,$target_width,$target_height);
		imagejpeg($img_new,$newpath,100);		
		return true;

	}
	
	
	static function processPhotoStringAsString ($string,$target_width,$target_height) {
	
		$image = BH_util::resizePhoto(imagecreatefromstring($string),$target_width,$target_height);	
		ob_start();
		imagejpeg($image,null,100);
		$string = ob_get_contents();
		ob_end_clean();
		return $string;

	}
	
	
	static function resizePhoto ($image,$target_width,$target_height) {
	
		$width = imagesx($image);
		$height= imagesy($image);
		$image_new = imagecreatetruecolor($target_width,$target_height);
		$height_mod = $height;
		$width_mod = $width;
		if ($height >= ($target_height / $target_width) * $width) {
			$height_mod = ($target_height / $target_width) * $width;
		} else {
			$width_mod = ($target_width / $target_height) * $height;
		}
		imagecopyresampled($image_new,$image,0,0,($width-$width_mod)/2,($height-$height_mod)/2,$target_width,$target_height,$width_mod,$height_mod);
		return $image_new;
	
	}


	static function countobj ($obj) {
	
		$count = 0;
		foreach ($obj as $thing) $count++;  // how to make this not error if object is not iteratable?
		return $count;
	
	}
	
	
	static function grab ($start,$end,$subject) {
	
		$escape = '\\/^$.[]|()?*+{}';
		for ($i=0; $i<strlen($escape); $i++) {
			$c = substr($escape,$i,1);
			$start = str_replace($c,"\\".$c,$start);
			$end = str_replace($c,"\\".$c,$end);			
		}
		$pattern = '/'.$start.'.*'.$end.'/';
		preg_match($pattern,$subject,$result);
		if (!sizeof($result)) return null;
		$result = $result[0];
		$result = substr($result,strlen($start),0-strlen($end));
		return $result;
		
	}
	
	
	static function prefix ($delimiter,$subject) {
	
		$a = explode($delimiter,$subject);
		return sizeof($a) ? $a[0] : null;
	
	}


	function suffix ($delimiter,$subject) {
	
		$a = explode($delimiter,$subject);
		return sizeof($a) ? $a[sizeof($a)-1] : null;
	
	}


	static function zeropad ($number,$digits) {
	
		for ($i=1; $i<=pow(10,$digits-1); $i=$i*10) {
			if ($number < $i && !($number == "0" && $i == 1) ) $number = "0".$number;
		}
		return $number;
		
	}
	
		
	static function singlespace ($string) {
	
		$pattern = '/[ ]+/';
		preg_match_all($pattern,$string,$matches);
		foreach ($matches[0] as $match) {
			$string = str_replace($match," ",$string);
		}
		return $string;
	
	}	


	static function depunctuate ($string) {
				  
		$pattern = "?~`!@#$%^&*()_-+={}|\\][:;\"\'><,./";
		if (func_num_args() > 1) {
			foreach (func_get_args() as $exception) $pattern = str_replace($exception,"",$pattern);
		}
		for ($i=0; $i<strlen($pattern); $i++) {
			$string = str_replace(substr($pattern,$i,1),"",$string);
		}
		return $string;
	
	}
	
	
	static function detab ($string) {
	
		$rep = func_num_args() > 1 ? func_get_arg(1) : ""; // keep this
		$string = str_replace("\t",$rep,$string);
		$string = str_replace("\r\n",$rep,$string);		
		$string = str_replace("\n",$rep,$string);
		$string = str_replace("\r",$rep,$string);		
		return $string;
	
	}


	static function linenormalize ($string) {

		$string = str_replace("\r\n","\n",$string);
		$string = str_replace("\r","\n",$string);
		return $string;	
	
	}
	
	
	static function shortdecimal ($string) {
	
		$parts = explode(".",$string);
		if (isset($parts[1])) {
			$parts[1] = substr($parts[1],0,2);
			if (strlen($parts[1]) == 1) $parts[1] .= "0";
		}
		return implode(".",$parts);
	
	}
	
	static function addcommas ($number) {
	
		$digits = array();
		if (strpos($number,".") !== false) {
			$decimals = BH_util::suffix(".",$number);
			$number = BH_util::prefix(".",$number);			
		}
		$output = "";
		for ($i=1; $i<=strlen($number); $i++) {
			$output = substr($number,-$i,1) . $output;
			if ($i % 3 == 0 && $i != strlen($number)) {
				$output = "," . $output;
			}
		}		
		if (isset($decimals)) {
			if (strlen($decimals) == 1) $decimals .= "0";
			$output .= ".".$decimals;
		}
		return $output;
		
	}

	static function saveFile ($dir, $contents) {	

		$parts = explode('/', $dir);
		$file = array_pop($parts);
		$dir = '';
		foreach($parts as $part) {
			if (!is_dir($dir .= "$part/")) mkdir($dir);
		}
		if (!file_put_contents("$dir"."$file", $contents)) return false;
		return true;

	}
	
	static function formatAsTime ($seconds) {

		if ($seconds >= 60) {
			$minutes = floor($seconds / 60);
			$seconds -= $minutes * 60;
			if ($minutes >= 60) {
				$hours = floor($minutes / 60);
				$minutes -= $hours * 60;
				if ($hours >= 24) {
					$days = floor($hours / 24);
					$hours -= $days * 24;
				}
			}
		}
		$time = "";
		if ($days) $time .= BH_util::zeropad($days,2).":";
		if ($hours) $time .= BH_util::zeropad($hours,2).":";
		if ($minutes) $time .= BH_util::zeropad($minutes,2).":";		
		if (!$minutes) $time .= ":";
		$time .= BH_util::zeropad($seconds,2);
		return $time;

	}
	
    static function relative_date ($time) {

        $today = strtotime(date('M j, Y'));
        $reldays = ($time - $today)/86400;
        if ($reldays >= 0 && $reldays < 1) {
            return 'today';
        } else if ($reldays >= 1 && $reldays < 2) {
            return 'tomorrow';
        } else if ($reldays >= -1 && $reldays < 0) {
            return 'yesterday';
        }
        if ($reldays > 0) {
            $reldays = floor($reldays);
            return 'in ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
        } else {
            $reldays = abs(floor($reldays));
            return $reldays . ' day'  . ($reldays != 1 ? 's' : '') . ' ago';
        }

    }	
    
    static function urlsToLinks ($str) {
     
        return ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $str);        
        
    }

}

?>