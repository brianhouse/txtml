<?php

class Module_get extends Module {

	protected function x ($user,$time,$input) {
		
		return $user->getVar($this->param("var")) !== null ? $user->getVar($this->param("var")) : true;
			
	}

}

?>