<?php

class Module_getfeed extends Module {

	protected function x ($user,$time,$input) {
		
		if ($feed = Object::load("Feed",$this->param("name"))) {
			return $feed->getVar($this->param("var")) !== null ? $feed->getVar($this->param("var")) : true;
		}
		return true;
			
	}

}

?>