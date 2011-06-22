<?php

class Module_pool extends Module {

	protected function x ($user,$time,$input) {

		// stops execution if it works, otherwise keeps going	
		// it would be great if this could work off of the path. but right now, the path isnt fully loaded
	
		if ($this->param("states")) {
			$states = $this->param("states");
			$states = explode(",",$states);
			foreach ($states as $key => $state) {
				if ($user->getVar($state) === "true") {
					unset($states[$key]);
				}
			}
			$states = array_slice($states,0); // re-index
			if (!sizeof($states)) return true;
			$state = $states[rand(0,sizeof($states)-1)];
			$state = $user->state()->collection() . ":" . $state;
			if ( $destination = State::load($state) ) {
				$user->setState($destination);			
				return false;			
			}		
		}
		
		////
		
		// always keeps going
		
		if ($this->param("blocks")) {
			$blocks = $this->param("blocks");
			$blocks = explode(",",$blocks);
			foreach ($blocks as $key => $block) {
				if ($user->getVar($block) === "true") {
					unset($blocks[$key]);
				}
			}
			$blocks = array_slice($blocks,0); // re-index
			if (!sizeof($blocks)) return true;
			$block = $blocks[rand(0,sizeof($blocks)-1)];
			$block = $user->state()->collection() . ":" . $block;
			if ( $destination = Block::load($block) ) {
				return $destination->execute($user,$time,$input);
			}						
		}
		
		return true;
	
	}

}

?>