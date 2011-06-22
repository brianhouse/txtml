<?php

class Module_exit extends Module {

	protected function x ($user,$time,$input) {
	
		$return = "";
		foreach ($this->submods as $submod) {
			$r = $submod->execute($user,$time,$input);
			if ($r === false) return false;
			if ($r !== true) $return .= $r;
		}
		return $return ? $return : true;
	
	}

}

?>