<?php

class Module_include extends Module {

	protected function x ($user,$time,$input) {
	
		if ( $block = Block::load($this->param("block")) ) {		
			return $block->execute($user,$time,$input);
		}		
		return true;
		
	}
	
}

?>