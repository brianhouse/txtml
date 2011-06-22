<?php

class Module_stopwatch extends Module {

	protected function x ($user,$time,$input) {
		
		$swtime = date("Y-m-d H:i:00",$time);		
		$user->setVar("stopwatch",$swtime);
		$user->clearSwtriggers();

		return true;
	
	}

}

?>