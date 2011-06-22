<?php

class Module_multiply extends Module {

	protected function x ($user,$time,$input) {

		$value = $user->getVar($this->param("var")) !== null ? $user->getVar($this->param("var")) : 1;
		$value = $this->number($value);

		if ($this->param("value") !== null) {
			$multiply = floatval($this->param("value"));
			BH_util::log("--> $value * $multiply = ".($value * $multiply));
			$value *= $multiply;
		}
		
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;			
			if ($c !== true) {
				$multiply = $this->number($c);
				BH_util::log("--> $value * $multiply = ".($value * $multiply));			
				$value *= $multiply;
			}
		}				

		if ($this->param("float") && $this->param("float") == "false") {
			$value = floor($value);
		} else {
			$value = BH_util::shortdecimal($value);			
		}
		
		if ($this->param("var")) {
			$user->setVar($this->param("var"),strval($value));
		}
		return strval($value);
	
	}

	
	private function number ($value) {
		
		if (!is_numeric($value)) {
			$format = new Format_number();
			$value = $format->process($value);
		}		
		return $value;
		
	}

}

?>