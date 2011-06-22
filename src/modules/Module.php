<?php

abstract class Module {		
		
		
	private $params = array();	
	protected $submods = array();
		
		
	public function __construct ($xml) {

		foreach ($xml->attributes() as $key => $value) {
			$this->params[$key] = utf8_decode((string)$value);
			if ($key != "string") $this->params[$key] = strtolower(trim($this->params[$key]));
		}	
		
		foreach ($xml->children() as $name => $submods) {
			$block = strtolower(utf8_decode($name));
			$block = str_replace("-","",$block);		
			$class = "Module_$block";
			if (class_exists($class)) {
				$this->submods[] = new $class($submods);
			} else {
				BH_util::log("unsupported module [$class]");
			}
		}					
	
	}


	public function execute ($user,$time,$input) {
	
		$module = BH_util::suffix("_",get_class($this));
		$log = '<'.$module.'> ';
		foreach ($this->params as $param => $value) $log .= "$param [$value] ";
		BH_util::log($log);
		$return = $this->x($user,$time,$input);
		return $return;
	
	}
	
	
	abstract protected function x ($user,$time,$input);
	
	protected function param ($arg) { return isset($this->params[$arg]) ? $this->params[$arg] : null; }
			
	protected function params () { return $this->params; }			
			
	
	public function display ($prefix = "") {

		$name = explode("_",get_class($this));
		$module = $name[sizeof($name)-1];
		if (substr($module,0,2) == "if" && strlen($module) > 2) $module = substr($module,0,2)."-".substr($module,2);
		$output = $prefix."&lt;$module ";
		if (sizeof($this->params)) {
			foreach ($this->params as $param => $value) $output .= $param."=\"".$value."\" ";
		}
		if (!sizeof($this->submods)) {		
			$output .= "/&gt;\n";
		} else {
			$output = substr($output,0,-1);		
			$output .= "&gt;\n";		
			foreach ($this->submods as $submod) $output .= $submod->display($prefix."\t");
			$output .= $prefix."&lt;/$module&gt;\n";
		}
		return $output;
	
	}
	

}

?>