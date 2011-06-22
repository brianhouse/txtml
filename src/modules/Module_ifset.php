<?php

class Module_ifset extends Module {

	protected function x ($user,$time,$input) {
				
		$match = $user->getVar($this->param("var")) !== null ? true : false;
		
		if (!$match) return $this->returnElse($user,$time,$input);
		
		$return = "";
		foreach ($this->submods as $submod) {
			if (BH_util::suffix("_",get_class($submod)) == "else") continue;
			$r = $submod->execute($user,$time,$input);
			if ($r === false) return false;
			if ($r !== true) $return .= $r;
		}
		return $return ? $return : true;
	
	}
	
	
	private function returnElse ($user,$time,$input) {
	
		$return = "";
		foreach ($this->submods as $submod) {
			if (BH_util::suffix("_",get_class($submod)) != "else") continue;		
			$r = $submod->execute($user,$time,$input);
			if ($r === false) return false;
			if ($r !== true) $return .= $r;
		}
		return $return ? $return : true;	
	
	}

}

?>