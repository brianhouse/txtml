<?php

abstract class Object {


	private static $instances = array(); // cache
	private $type;
	private $id;
	private $name;
	
	protected $changed = false;
	protected $attributes = array();
	protected $vars;
	protected $comments;

		
	// separately loads all instances of a type into cache.	
	public static function loadAll ($class) {
	
		$type = strtolower($class);
		$objects = array();
		$db = BH_db::open();
		$db->call("SELECT name FROM {$type}s WHERE active=1");
		foreach ($db->getAll() as $object) {
			$objects[] = Object::load($type,$object['name']);
		}
		return $objects;
	
	}
	
	
	public static function saveState () {
		
		BH_util::log("saving state");		
		foreach (self::$instances as $type => $instances) {
			foreach ($instances as $index => $instance) {
				self::$instances[$type][$index]->destroy();
				unset(self::$instances[$type][$index]);
			}
			unset(self::$instances[$type]);
		}
		BH_util::log("saved state");
	
	}


	// loads from cache if possible, otherwise pulls from db. Will not create a new entry.
	public static function load ($class,$name) {
		
		if (!isset($class) || !isset($name)) {
			BH_util::log("null values sent for object load");		
			return null;
		}		
		$type = strtolower($class);
		if ( isset(self::$instances[$type]) && array_key_exists($name,self::$instances[$type]) ) {
//			BH_util::log("redundant load of $type [$name]");
			return self::$instances[$type][$name];
		}
		$db = BH_db::open();
		$db->call("SELECT * FROM {$type}s WHERE name='".$db->safe($name)."' AND active=1");
		if ($db->rows()) {
			$object = new $class($type,$db->get());
			self::$instances[$type][$name] = $object;
			return $object;	
		} else {
			BH_util::log("$type [$name] does not exist");
			return null;
		}
	
	}
	
	
	// loads from cache if possible, otherwise pulls from db. If doesnt exist, creates new entry.
	public static function create ($class,$name) {

		if (!isset($class) || !isset($name)) {
			BH_util::log("null values sent for object creation");		
			return null;
		}
		$type = strtolower($class);
		if ( isset(self::$instances[$type]) && array_key_exists($name,self::$instances[$type]) ) {
//			BH_util::log("redundant load of $type [$name]");
			return self::$instances[$type][$name];
		}	
		$db = BH_db::open();
		$db->call("SELECT * FROM {$type}s WHERE name='".$db->safe($name)."' AND active=1");
		if ($db->rows()) {
			$object = new $class($class,$db->get());
		} else {
			// create object
			if (class_exists($class)) {
				$attributes = eval("return $class::init('$name');");
				$attributes['name'] = $name;
				BH_util::log("created $type [$name]");
				$object = new $class($class,$attributes);				
			} else {
				BH_util::log("no [$type] object type");		
				return null;
			}
			$object->setup();			
		}
		self::$instances[$type][$name] = $object;
		return $object;

	}


	// actual constructor
	protected function __construct ($class,$attributes) {

		$type = strtolower($class);
		$this->type = $type;
		$this->id = $attributes['id'];
		$this->name = $attributes['name'];
		foreach ($attributes as $attribute => $value) {
			if ($attribute == "id") continue;
			if ($attribute == "name") continue;
			$this->attributes[$attribute] = $value;
		}
		BH_util::log("loaded $type [".$this->name()."]");					
	
	}


	// if object has been changed, updates the db
	public function destroy () {

		unset($this->instances[$this->type()][$this->name()]);		
		if ($this->changed) {
			$db = BH_db::open();
			if (sizeof($this->attributes)) {
				$fields = "";
				foreach ($this->attributes as $key => $attribute) {
					if ($attribute === null) {
						$fields .= "$key=null, ";
					} else {
						$fields .= "`$key`='".$db->safe($attribute)."', ";
					}
				}
				$fields = substr($fields,0,-2);
				$db->call("UPDATE ".$this->type()."s SET $fields WHERE id=".$this->id());
			}
			if (isset($this->vars)) {
				foreach ($this->vars as $var => $value) {
					if (empty($var)) continue;
					if (!is_string($value) || $value === "") {
						$db->call("DELETE FROM {$this->type}_vars WHERE {$this->type}_id={$this->id} AND var='".$db->safe($var)."'");
						continue;
					}									
					$db->call("SELECT id FROM {$this->type}_vars WHERE {$this->type}_id={$this->id} AND var='".$db->safe($var)."'");		
					if ($db->rows()) {
						$id = $db->get("id");
						$db->call("UPDATE {$this->type}_vars SET value='".$db->safe($value)."' WHERE id=$id");
					} else {
						$db->call("INSERT INTO {$this->type}_vars ({$this->type}_id,var,value) VALUES ({$this->id},'".$db->safe($var)."','".$db->safe($value)."')");
					}
				}
			}
			BH_util::log("wrote changes to ".$this->type()." [".$this->name()."]");
		}
	
	}
	
	
	abstract protected function setup ();

	public function type () { return $this->type; }
	
	public function id () { return $this->id; }
	
	public function name () { return $this->name; }
	

	public function getVar () {
	
		if (!isset($this->vars)) {
			$db = BH_db::open();
			$db->call("SELECT var,value FROM {$this->type}_vars WHERE {$this->type}_id={$this->id}");
			$this->vars = array();			
			foreach ($db->getAll() as $var) {
				if (empty($var['var'])) continue;
				$this->vars[$var['var']] = $var['value'];
			}						
			BH_util::log("loaded variables for ".$this->type." [".$this->name()."]");
		}
		if (!func_num_args() || !isset($this->vars[func_get_arg(0)]) || $this->vars[func_get_arg(0)] === "") return null;
		return $this->vars[func_get_arg(0)];
	
	}
	
	
	public function setVar ($var,$value) {
	
		if (!isset($value) || !is_string($value) || $value === "") return;
		$this->getVar();
		$this->changed = true;
		$this->vars[$var] = $value;
		BH_util::log("set variable [$var] to [$value] for ".$this->type." [".$this->name()."]");
	
	}


	public function unsetVar ($var) {

		$this->getVar();
		if (isset($this->vars) && array_key_exists($var,$this->vars)) {
			$this->vars[$var] = "";
			$this->changed = true;
		}
	
	}	

	
}

?>
