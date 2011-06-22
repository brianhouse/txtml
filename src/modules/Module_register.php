<?php

class Module_register extends Module {

	protected function x ($user,$time,$input) {
		
		$string = "";
		if ($this->param("value") !== null) $string = $this->param("value");
		foreach ($this->submods as $submod) {
			if (BH_util::suffix("_",get_class($submod)) == "else") continue;		
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $string .= $c;
		}		

		if ($string === "") $string = $input;
		if ($string === "") return $this->returnElse($user,$time,$input);
		
		$phstring = "";
		for ($i=0; $i<strlen($string); $i++) {
			$char = substr($string,$i,1);
			if (is_numeric($char)) $phstring .= $char;
		}

		$pattern = "/^1?[0-9]{10}$/";
		preg_match($pattern,$phstring,$matches);
		if (sizeof($matches)) $phone = $matches[0];
		
		if (!isset($phone)) {
			BH_util::log("no number given");		
			return $this->returnElse($user,$time,$input);
		}
		
		$address = $phone;
				
		$user2 = Object::create("User",$address);
		if (!$user2->state()) {
			$state = State::load($this->param("state"));
			if ($state && $state->start()) {
				foreach ($this->params() as $param => $value) {
					if ($param == "state" || $param == "vars") continue;
					$user2->setVar($param,$value);
				}
				if ($this->param("vars")) {
					foreach (explode(",",$this->param("vars")) as $varname) {
						if ($user->getVar($varname)) {
							$new_varname = str_replace("register_","",$varname);
							$user2->setVar($new_varname,$user->getVar($varname));
						}
					}
				}
				$user2->setVar("inviter",$user->address());
				$user2->setState($state);		
			} else {			
				BH_util::log("orphan user");
				$user2->deactivate();
			}			
		}
		
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