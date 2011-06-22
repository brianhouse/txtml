<?php

class Module_quit extends Module {

	protected function x ($user,$time,$input) {

		if ($this->param("silent") && $this->param("silent") == "true") {
			$user->deactivate();
			return false;
		}
		if ($user->state()->quit()) {
			$user->setState($user->state()->quit());
		}
		$user->deactivate();
		return false;
	
	}

}

?>