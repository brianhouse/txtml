<?php

class Module_ifinput extends Module {

	protected function x ($user,$time,$input) {
		
		$match = false;
		
		$string = $input;
		if (!isset($string) || $string === "") return $this->returnElse($user,$time,$input);
				
		$class = $this->param("format") ? "Format_".$this->param("format") : "Format_lowercase";
		$format = new $class();
		$string = $format->process($string);		
		
		if ($this->param("var") !== null && $user->getVar($this->param("var")) !== null) {
			BH_util::log("--> var: ".$this->param("var"));
			$value = $user->getVar($this->param("var"));
			$relation = $this->param("relation") ? $this->param("relation") : "=";
			BH_util::log("--> relation: $relation");
			$var_match = false;
			switch ($relation) {
				case ">":
					if ($string > $value) $var_match = true;
				break;
				case "<":
					if ($string < $value) $var_match = true;
				break;
				case ">=":
					if ($string >= $value) $var_match = true;
				break;
				case "<=":
					if ($string <= $value) $var_match = true;
				break;				
				case "!=":
					if ($string != $value) $var_match = true;
				break;				
				default:
					if ($string == $value) $var_match = true;
			}			
		} else {
			$var_match = false;
		}
		
		if ($this->param("pattern")) {
			$pattern_match = Language::match($string,$this->param("pattern"));		
		} else if ($this->param("patternv") && $user->getVar($this->param("patternv")) !== null) {
			$pattern_match = Language::match($string,$user->getVar($this->param("patternv")));		
		} else {
			$pattern_match = false;
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
				
		$match = $var_match || $pattern_match || $value_match;
		
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