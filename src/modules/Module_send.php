<?php

class Module_send extends Module {

	protected function x ($user,$time,$input) {
	
		if ( $destination = State::load($this->param("state")) ) {
			$user->setState($destination);			
			return false;			
		}		
		return true;
	
	}

}

?>