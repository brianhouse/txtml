<?php

class Module_ellis extends Module {

	protected function x ($user,$time,$input) {

		$format = new Format_name();
		$name = $format->process($input);
		$name = explode(" ",$name);
		if (empty($name[1])) {
			$name[1] = $name[0];
			$name[0] = "";
		}
			
		if ( $callback_state = State::load($this->param("state")) ) {
			$ellis = new Requestor_ellis();
			$ellis->request($user,$callback_state,array('firstname'=>$name[0],'lastname'=>$name[1]));
		}
	
	}		
		
}