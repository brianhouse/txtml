<?php

class BH_config {


	static function loadClass ($path) {
	
		$package = BH_util::prefix(".",basename($path));
		require_once($path);		
		//error_log("loading class $package");		
		$dir = dirname($path);
		$handle = opendir($dir);
		$files = array();
		while ($file = readdir($handle)) {
			if (BH_util::prefix("_",basename($file)) != $package) continue;
			if (substr($file,-4) != ".php") continue;
			$files[] = $dir."/".$file;
		}		
		sort($files);
		foreach ($files as $file) {
			//error_log("including file $file");
			require_once($file);
		}
	
	}


	static function basedir () {
	
		static $basedir = ".";
		if (func_num_args()) $basedir = func_get_arg(0);
		return $basedir;
	
	}

	
	static function get ($index) {

		$config = BH_config::load();
		if (isset($config->settings[$index])) {
			if (func_num_args() == 2) {
				if (isset($config->settings[$index][func_get_arg(1)])) {
					return $config->settings[$index][func_get_arg(1)];
				} else {
					return null;
				}
			} else {
				return $config->settings[$index];
			}
		} else {
			return null;
		}
	
	}
		
	static function load () {
		
		if (!isset(self::$instance)) {
			self::$instance = new BH_config();
		}
		return self::$instance;
	
	}
	
	
	static $instance;
	private $settings;
	
	private function __construct () {  // just one category deep
				
		$configpath = BH_config::basedir()."/config.xml";
		if (!$xml = @simplexml_load_file($configpath)) {
			BH_util::log("BH: config.xml configuration file not found [$configpath] or invalid");
		} else {
			foreach ($xml as $key => $value) {
				if (BH_util::countobj($value)) {
					$this->settings[(string)$key] = array();
					foreach ($value as $subkey => $subvalue) {
						$this->settings[(string)$key][(string)$subkey] = (string)$subvalue;
					}
				} else {
					$this->settings[(string)$key] = (string)$value;
				}
			}
		}	
	
//		print_r($this->settings);
	
	}
	
	private function __clone () { }

}


?>