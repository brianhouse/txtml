<?php

class Module_if extends Module {

	protected function x ($user,$time,$input) {
		
		$match = false;
		
		$string = $user->getVar($this->param("var"));
		if (!isset($string) || $string === "") return $this->returnElse($user,$time,$input);
		
		$string = strtolower($string);
		
		if ($this->param("var2") !== null && $user->getVar($this->param("var2")) !== null) {
			BH_util::log("--> var2 (".$this->param("var2").") [$value]");
			$value = $user->getVar($this->param("var2"));
			$relation = $this->param("relation") ? $this->param("relation") : "=";
			BH_util::log("--> relation: $relation");
			$value_match = false;
			switch ($relation) {
				case ">":
					if ($string > $value) $value_match = true;
				break;
				case "<":
					if ($string < $value) $value_match = true;
				break;
				case ">=":
					if ($string >= $value) $value_match = true;
				break;
				case "<=":
					if ($string <= $value) $value_match = true;
				break;				
				case "!=":
					if ($string != $value) $value_match = true;
				break;				
				default:
					if ($string == $value) $value_match = true;
			}			
		} else {
			$var_match = false;
		}
		
		if ($this->param("value") !== null) {
			BH_util::log("--> values: ".$this->param("value"));
			$relation = $this->param("relation") ? $this->param("relation") : "=";
			BH_util::log("--> relation: $relation");
			$value_match = false;
			$values = explode(",",$this->param("value"));
			foreach($values as $value) {
				switch ($relation) {
					case ">":
						if ($string > $value) $value_match = true;
					break;
					case "<":
						if ($string < $value) $value_match = true;
					break;
					case ">=":
						if ($string >= $value) $value_match = true;
					break;
					case "<=":
						if ($string <= $value) $value_match = true;
					break;				
					case "!=":
						if ($string != $value) $value_match = true;
					break;				
					default:
						if ($string == $value) $value_match = true;
				}
			}
		} else {
			$value_match = false;
		}
				
		$match = $var_match || $value_match;
		
		if (!$match) return $this->returnElse($user,$time,$input);
		
		$return = "";
		BH_util::log("--> match: true");		
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
		BH_util::log("--> match: false");
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