<?php

class Module_add extends Module {

	protected function x ($user,$time,$input) {

		$value = $user->getVar($this->param("var")) !== null ? $user->getVar($this->param("var")) : 0;
		$value = $this->number($value);

		if ($this->param("value") !== null) {
			$add = floatval($this->param("value"));
			BH_util::log("--> $value + $add = ".($value + $add));
			$value += $add;
		}
		
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;			
			if ($c !== true) {
				$add = $this->number($c);
				BH_util::log("--> $value + $add = ".($value + $add));			
				$value += $add;
			}
		}				

		if ($this->param("float") && $this->param("float") == "false") {
			$value = floor($value);
		} else {
			$value = BH_util::shortdecimal($value);			
		}
		
		if ($this->param("var")) {
			$user->setVar($this->param("var"),strval($value)); //preserve string
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