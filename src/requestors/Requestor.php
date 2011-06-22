<?php

abstract class Requestor {
		
	
	public function request (User $user,State $callback_state,$params) {
		
		BH_util::log("Requestor initialized for user [".$user->address()."] callback [".$callback_state->name()."]");		
		
		$pid = pcntl_fork();
		if ($pid) {
			$result = $this->getData($params);

			BH_util::log("--> [requestor] setting state to [".$callback_state->name()."]");
			$data = array();
			$data['action'] = "send";			
			$data['user'] = $user->address();
			$data['state'] = $callback_state->name();
			$data = http_build_query($data);
			BH_net::scrape(BH_config::get("api")."?".$data);			

			BH_util::log("--> [requestor] setting request var to [$result]");			
			$data = array();
			$data['action'] = "set";			
			$data['user'] = $user->address();
			$data['var'] = "request";
			$data['value'] = $result;
			$data = http_build_query($data);
			BH_net::scrape(BH_config::get("api")."?".$data);			
			
			pcntl_waitpid($pid,$status,WUNTRACED);
			BH_util::log("--> [requestor] exiting request process, status [$status]");
			exit; // return here could be a big problem, because we have objects still in memory, duplicated (and exit is ok?)
		}
		BH_util::log("--> continuing execution");
		return;
		
	}
	
	
	abstract protected function getData ($params);
	

}

?>