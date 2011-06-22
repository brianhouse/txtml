<?php

class BH_db {


	private static $instance;  // singleton
	private $db = null;
	private $resultset = null;
	private $result_row = null;
	private $verbose = false;
	private $locked = false;
	

	static function open () {
	
		if (!isset(self::$instance)) {
			self::$instance = new BH_db();
		}
		return self::$instance;
	
	}
	
	
	public function close () {
	
		if ($this->locked) $this->unlock();
		@mysql_close($this->db()); 
		$this->db = null;
		if ($this->verbose)	BH_util::log("-----db: closed");		
		
	}
	

	private function __construct () {
	
		$this->connect();
		if ($this->verbose)	BH_util::log("-----db: connected ".$this->db);
		
	}

	private function __clone () { }

	public function __destruct () { if ($this->db) $this->close(); }


	private function db () { return $this->db; }
	private function resultset () { return $this->resultset; }


	private function connect () {

		$server = BH_config::get("db");	
		$this->verbose = $server['verbose'] == "true" ? true : false;	
		if ( $this->db() == null || !@mysql_ping($this->db()) ) {
			$this->db = @mysql_connect($server['host'],$server['username'],$server['password']);
			if ($this->db() == null) {
				BH_util::log("-----db error: could not connect to [".$server['host']."] with user [".$server['username']."]");
				exit;
			}
			if ($this->verbose) BH_util::log("-----db: connected to [".$server['host']."] with user [".$server['username']."]");			
			if (!@mysql_select_db($server['database'],$this->db())) {
				BH_util::log("-----db error: could not select database [".$server['database']."]");
				exit;
			}
		}
		
	}


	public function call ($string) {
	
		// the insert id command only works with id type INT (up to 4billion)
		if ($this->verbose) BH_util::log("-----db: ".$string);
		$this->connect();
		$this->result_row = null;
		if ( $this->resultset = mysql_query($string,$this->db()) ) {
			if (strtoupper(substr($string,0,6)) == "INSERT") {
				return mysql_insert_id($this->db());
			} else {
				return true;
			}
		} else {
			BH_util::log("-----db error: ".$string);		
			BH_util::log("-----db error: ".mysql_error());
			return false;
		}
	
	}
	
	
	public function get () {
	
		if (func_num_args()) {
			if ($this->result_row == null) $this->result_row = mysql_fetch_assoc($this->resultset());
			return isset($this->result_row[func_get_arg(0)]) ? $this->result_row[func_get_arg(0)] : null;
		} else {
			return mysql_fetch_assoc($this->resultset());
		}
	
	}
	
	
	public function getAll () {
	
		// inconsistently, the "while ($var = get())" structure does not work when embedded in objects; this method makes it happen explicitly
		$result = array();
		while ($r = mysql_fetch_assoc($this->resultset())) {
			$result[] = $r;
		}
		return $result;
	
	}
	
	
	public function rows () {

		return mysql_num_rows($this->resultset());
	
	}
	

	public function safe ($string) {
	
		$this->connect();
		return mysql_real_escape_string($string,$this->db());
	
	}

	
	public function lock () {
	
		$tables = array();
		$this->call("SHOW TABLES");
		foreach ($this->getAll() as $row) {
			foreach ($row as $table) $tables[] = $table;
		}
		$tables = implode(" WRITE, ",$tables);
		$tables .= " WRITE";
		$this->call("LOCK TABLES $tables");
		$this->locked = true;
	
	}
	
	
	public function unlock () {
	
		$this->call("UNLOCK TABLES");
		$this->locked = false;
	
	}

	
	public function xml () {
	
		$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?".">\n";
		$xml .= "<result>\n";
		while ($row = mysql_fetch_assoc($this->resultset())) {
			$xml .= "\t<entry>\n";
			foreach ($row as $key => $value) {
				$xml .= "\t\t<".$key."><![CDATA[".str_replace("]]>","---",$value)."]]></".$key.">\n";
			}
			$xml .= "\t</entry>\n";
		}
		$xml .= "</result>\n";
		return $xml;
	
	}
	
	public function requestToField ($param) {
	
		if (!isset($_REQUEST)) return null;
		return isset($_REQUEST[$param]) ? "'".$this->safe($_REQUEST[$param])."'" : "null";		
		
	}


}


?>