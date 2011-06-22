<?php

class Module_random extends Module {

	protected function x ($user,$time,$input) {
		
		$r = $this->submods[rand(0,sizeof($this->submods)-1)]->execute($user,$time,$input);
		if ($r === false) return false;
		if ($r !== true) return $r;
		return true;
	
	}

}

?>