<?php

class Module_txt extends Module {

	protected function x ($user,$time,$input) {
				
		if (!$content = $this->param("string")) $content = "";
		foreach ($this->submods as $submod) {
			$c = $submod->execute($user,$time,$input);
			if ($c === false) return false;
			if ($c !== true) $content .= $c;
		}		
										
		Interpreter::send($user,trim($content));		
		
		return true;

	}

}

?>