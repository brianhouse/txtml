<?php

class Module_unset extends Module {

	protected function x ($user,$time,$input) {

		$user->unsetVar($this->param("var"));
		return true;
	
	}

}

?>