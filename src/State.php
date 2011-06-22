<?php

class State {

		
	private $name;
	private $collection;
	private $start = false;
	private $help = null;
	private $quit = null;
	private $timeout = null;
	private $prefix = null;
	private $filename = null;
	private $xml;


	public static function default_state () {
	
		$db = BH_db::open();
		$db->call("SELECT state FROM default_state LIMIT 1");
		$name = $db->get("state");
		return empty($name) ? null : self::load($name);
	
	}


	public static function keyword ($token) {
	
		$db = BH_db::open();
		$db->call("SELECT state FROM router WHERE keyword LIKE '".$db->safe($token)."'");
		$name = $db->get("state");
		return empty($name) ? null : self::load($name);
	
	}

	
	public static function load ($name) {

		static $instances = array(); // singleton group

		if ( isset($instances) && array_key_exists($name,$instances) ) {
			BH_util::log("redundant load of state [$name]");
			return $instances[$name];
		}
		
		$state = new State($name);
		if (isset($state->name)) {
			$instances[$name] = $state;
			BH_util::log("state [".$state->name()."] loaded");
			return $state;
		}
		
		return null;

	}
	
	
	private function __construct ($arg_name) { 

		$db = BH_db::open();
		$db->call("SELECT start,txtml,filename FROM states WHERE name='".$db->safe($arg_name)."'");
		if (!$db->rows()) {
			BH_util::log("state [$arg_name] not found");
			return null;
		}

		$this->name = $arg_name;
		$this->collection = BH_util::prefix(":",$this->name);
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
		if (isset($xml['prefix'])) $this->prefix = (string)$xml['prefix'];
		if (isset($xml['timeout'])) $this->timeout = intval((string)$xml['timeout']);		
	
		$this->xml = $xml;
			
	}
	
	
	public function name () { return $this->name; }

	public function collection () { return $this->collection; }
	
	public function start () { return $this->start; }
	

	public function help () {
	
		if (!empty($this->help) && $help = State::load($this->help)) return $help;
		return null;
		
	}
	
	
	public function quit () {
	
		if (!empty($this->quit) && $quit = State::load($this->quit)) return $quit;
		return null;
		
	}


	public function prefix () {
	
		if (!empty($this->prefix) && $prefix = State::load($this->prefix)) return $prefix;
		return null;
	
	}


	public function timeout () { return $this->timeout; }


	public function handle ($event,$user,$time,$input) {
	
		BH_util::log("state [".$this->name()."] event [$event] user [".($user ? $user->address() : "")."] time [$time] input [$input]");

		$return = "";
		foreach ($this->xml as $block => $contents) {
			$block = strtolower(utf8_decode($block));
			if ($event && $block != $event) continue;
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
		$output .= "START [".($this->start ? "YES" : "NO")."] PREFIX [".$this->prefix."] HELP [".$this->help."] QUIT [".$this->quit."] TIMEOUT [".$this->timeout."]\n\n";
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
		
	
	public function trigger () {
	
		foreach ($this->xml as $block => $contents) {
			$block = strtolower(utf8_decode($block));
			if ($block == "time") return true;
		}
		return false;
	
	}
	
	
	public function filename () { return $this->filename; }
		
	
}

?>