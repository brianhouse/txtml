<?php

class Module_return extends Module {

	protected function x ($player,$time,$input) {
			
		$player->setState($player->lastState());
		return false;
	
	}

}

?>