<?php

class User extends Object {


	// runtime only
	private $state;
	
	
	protected static function init ($address) {

		$pending = true;
		$db = BH_db::open();	
		$now = date("Y-m-d H:i");
		$attributes['start'] = $now;
		$attributes['last'] = $now;
		$attributes['timeout'] = null;
		$attributes['trig'] = 0;
		$attributes['id'] = $db->call("INSERT INTO users (name,active,start,last) VALUES ('".$db->safe($address)."',1,'$now','$now')");
		return $attributes;
		
	}
	

	public static function purge () {
	
		$db = BH_db::open();
		BH_util::log("purging timed out users");
		$db->call("UPDATE users SET active=0 WHERE active=1 AND timeout IS NOT NULL AND timeout <= '".date("Y-m-d H:i")."'");
	
	}

	
	public static function loadWaiting () {
	
		$users = array();
		$db = BH_db::open();
		$db->call("SELECT name FROM users WHERE active=1 AND trig=1 ORDER BY last");
		foreach ($db->getAll() as $user) {
			$users[] = Object::load("user",$user['name']);
		}
		return $users;
	
	}
		
	
	protected function setup () {
	
		$this->areaLookup();
	
	}
	
	
	public function address () { return $this->name(); }
		
	public function start () { return $this->attributes['start']; }
	
	public function last () { return $this->attributes['last']; }
		
	private function setLast ($arg) { $this->attributes['last'] = $arg; }	
	
	
	public function lastState () { 

		$db = BH_db::open();
		$db->call("SELECT state FROM path WHERE user_id=".$this->id()." ORDER BY time DESC, id DESC limit 2");
		if ($db->rows() < 2) return $this->state;
		$i=0;
		foreach ($db->getAll() as $path) if ($i++ == 1) break;		
		return State::load($path['state']);
		
	}
	

	public function state () { 
			
		if (!isset($this->state)) {
			$db = BH_db::open();
			$db->call("SELECT state FROM path WHERE user_id=".$this->id()." ORDER BY time DESC, id DESC limit 1");
			$this->state = $db->rows() ? State::load($db->get('state')) : null;
		}
		return $this->state;

	}


	public function setState (State $arg_state) {

		$time = time();		
		if ($this->state()) {
			$result = true;
			if ($this->state()->prefix()) {
				$result = $this->state()->prefix()->handle("exit",$this,$time,null);
			}
			if ($result !== false) $this->state()->handle("exit",$this,$time,null);
		}

		$this->changed = true;
		$db = BH_db::open();
		$this->path[] = $arg_state;
		$this->state = $arg_state;
		$this->setLast(date("Y-m-d H:i",$time));
		$this->clearTriggers();
		$db->call("INSERT INTO path (user_id,state,time) VALUES ({$this->id()},'{$this->state->name()}','".date("Y-m-d H:i",$time)."')");				
		BH_util::log("set [".$this->address()."] to state [".$this->state()->name()."]");
		
		$this->attributes['timeout'] = $this->state()->timeout() == null ? null : date("Y-m-d H:i",$time+($this->state->timeout()*60));		
		$this->attributes['trig'] = $this->state()->trigger() ? 1 : 0;

		$result = true;		
		if ($this->state()->prefix()) {
			$result = $this->state()->prefix()->handle("enter",$this,$time,null);
		}
		if ($result !== false) $this->state()->handle("enter",$this,$time,null);
	
	}
	
	
	public function clearTriggers () {
	
		$this->getVar(null);
		if (!empty($this->vars)) {
			foreach ($this->vars as $key => $value) {
				if (substr($key,0,5) == "trig_") $this->unsetVar($key);
			}
		}
		BH_util::log("cleared triggers for [".$this->address()."]");
		
	}
	

	public function clearSwtriggers () {
	
		$this->getVar(null);
		if (!empty($this->vars)) {
			foreach ($this->vars as $key => $value) {
				if (substr($key,0,7) == "swtrig_") $this->unsetVar($key);
			}
		}
		BH_util::log("cleared swtriggers for [".$this->address()."]");
		
	}	


	public function deactivate () {
	
		$this->changed = true;		
		$this->attributes['active'] = 0;
		BH_util::log("deactivate user [".$this->address()."]");
		
	}
		

	private function areaLookup () {
	
		BH_util::log("areaLookup");
		
		$phone = BH_util::prefix(":",$this->address());
		if (substr($phone,0,1) == "+") $phone = substr($phone,1);
		if (substr($phone,0,1) == "1") $phone = substr($phone,1);
		if (strlen($phone) != 10) {
			BH_util::log("--> not a standard phone number");
			return;
		}
					
		// http://www.bennetyee.org/ucsd-pages/area.html	
		$data = file(BH_config::basedir()."/data/areacodes.xml");
		$codes = array();
		foreach ($data as $line) {
			$line = explode("\t",$line);
			$code = $line[0];
			$state = $line[1];
			$country = $line[2];
			$description = trim($line[3]);
			$data[$code] = array( "country"=>$country, "state"=>$state, "description"=>$description );
		}				
		
		$code = substr($phone,0,3);
		if (!isset($data[$code])) {
			BH_util::log("--> area code not found");		
			return;
		}		
		
		$this->setVar("loc_country",$data[$code]['country']);
		$this->setVar("loc_state",$data[$code]['state']);
		$this->setVar("loc_description",$data[$code]['description']);
		
		BH_util::log("--> user area code [".$data[$code]['country']." ".$data[$code]['state']." ".$data[$code]['description']."]");		
	
	}


}

?>