<?php

class Module_nothing extends Module {

	protected function x ($user,$time,$input) {
	
		foreach ($this->submods as $submod) {
			$submod->execute($user,$time,$input);
		}
		return true;
	
	}

}

?>