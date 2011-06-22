<?php

class Module_subtract extends Module {

	protected function x ($user,$time,$input) {

		if ($user->getVar($this->param("var")) !== null) {
			$value = $user->getVar($this->param("var"));
			$value = $this->number($value);			
		}

		if ($this->param("value") !== null) {
			$subtract = floatval($this->param("value"));
			if (!isset($value)) {
				$value = $subtract;			
			} else {
				BH_util::log("--> $value - $subtract = ".($value - $subtract));						
				$value -= $subtract;
			}
			
		}
		
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;			
			if ($c !== true) {
				$subtract = $this->number($c);
				if (!isset($value)) {
					$value = $subtract;			
				} else {
					BH_util::log("--> $value - $subtract = ".($value - $subtract));							
					$value -= $subtract;
				}
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