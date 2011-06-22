<?php

class Feed extends Object {


	public static function updateAll () {
		
		$classes = get_declared_classes();
		foreach ($classes as $class) {
			if (substr($class,0,7) != "Feeder_") continue;
			$name = BH_util::suffix("_",$class);
			$feed = Object::create("Feed",$name);			
			$feed->update();			
			$feed->destroy();
		}
		
		BH_util::log("================================");			
		BH_util::log("updated all feeds");
		BH_util::log("================================");			
	
	}


	protected static function init ($name) {

		$db = BH_db::open();	
		$attributes['id'] = $db->call("INSERT INTO feeds (name,active) VALUES ('$name',1)");
		return $attributes;
		
	}
		
		
	protected function setup () { }	
		
	
	public function update () {
	
		$class = "Feeder_".$this->name();
		if (class_exists($class)) {		
			$feeder = new $class();		
			$feeder->update($this);
			BH_util::log("updated feed [".$this->name()."]");								
		} else {
			BH_util::log("feed [".$this->name()."] has no feeder");
		}
	
	}
		
	
	public function remove () {
	
		$db = BH_db::open();
		$db->call("DELETE FROM feeds WHERE id=".$this->id());
		$db->call("DELETE FROM feed_vars WHERE feed_id=".$this->id());		
		BH_util::log("delete feed [".$this->name()."]");
		unset($this->instances[$this->type()][$this->name()]);
		unset($this);

	}	


}

?>