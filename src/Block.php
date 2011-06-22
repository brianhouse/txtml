<?php

class Block {

	
	private $name = null;
	private $filename = null;
	private $xml;
	

	public static function load ($name) {

		static $instances = array(); // singleton group

		if ( isset($instances) && array_key_exists($name,$instances) ) {
			BH_util::log("redundant load of block [$name]");
			return $instances[$name];
		}
		
		$block = new Block($name);
		if (isset($block->name)) {
			$instances[$name] = $block;
			BH_util::log("block [".$block->name()."] loaded");
			return $block;
		}
		
		return null;

	}
	
	
	private function __construct ($arg_name) { 

		$db = BH_db::open();
		$db->call("SELECT txtml,filename FROM blocks WHERE name='".$db->safe($arg_name)."'");
		if (!$db->rows()) {
			BH_util::log("block [$arg_name] not found");
			return null;
		}

		$this->name = $arg_name;
		$this->start = $db->get("start") == 1 ? true : false;
		$this->filename = $db->get("filename");
		
		$string = $db->get("txtml");
		$string = BH_util::detab($string);
						
		$pattern = "/>[^>]+</";
		preg_match_all($pattern,$string,$matches,PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		foreach ($matches as &$match) {
			$match[0] = substr($match[0],1,-1);
			$match[1]++;
		}

		$strings = array();
		$strings[] = sizeof($matches) ? substr($string,0,$matches[0][1]) : $string;
		for ($i=0; $i<sizeof($matches); $i++) {
			if (isset($matches[$i+1])) {
				$strings[] = substr($string,$matches[$i][1],($matches[$i+1][1]-$matches[$i][1]));
			} else {
				$strings[] = substr($string,$matches[$i][1],(strlen($string)-$matches[$i][1]));
			}
			$strings[sizeof($strings)-1] = "<content string=\"".BH_util::xmlencode(BH_util::xmldecode($matches[$i][0]))."\" />".substr($strings[sizeof($strings)-1],strlen($matches[$i][0]));
		}
		$string = implode("",$strings);
						
		$xml = simplexml_load_string($string);
		
		if (isset($xml['help'])) $this->help = (string)$xml['help'];
		if (isset($xml['quit'])) $this->quit = (string)$xml['quit'];
		if (isset($xml['timeout'])) $this->timeout = intval((string)$xml['timeout']);		
	
		$this->xml = $xml;
			
	}
	
	
	public function name () { return isset($this->name) ? $this->name : null; }


	public function execute ($user,$time,$input) {
		
		BH_util::log("block [".$this->name()."] user [".($user ? $user->address() : "")."] time [$time] input [$input]");

		$return = "";
		foreach ($this->xml as $block => $contents) {
			$block = strtolower(utf8_decode($block));
			$block = str_replace("-","",$block);			
			$class = "Module_$block";
			if (class_exists($class)) {
				$block = new $class($contents);
				$c = $block->execute($user,$time,$input);
				if ($c === false) return false;
				if ($c !== true) $return .= $c;
			} else {
				BH_util::log("unsupported module $class");
			}
		}
		return $return;

	}
	
	
	public function display () {
			
		$output = "";
		foreach ($this->xml as $block => $contents) {
			$block = strtolower(utf8_decode($block));	
			$block = str_replace("-","",$block);			
			$class = "Module_$block";
			if (class_exists($class)) {		
				$block = new $class($contents);	
				$output .= $block->display();
				$output .= "\n";
			}
		}
		return $output;

	}
	
	
	public function collection () {
	
		return BH_util::prefix(":",$this->name());
	
	}
		
	
	public function filename () { return $this->filename; }
		
	
}

?>