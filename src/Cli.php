<?php

class Cli {


	private static $instance;
	private static $host = "0.0.0.0";
	private static $port = 8000;
	
	
	static function start () {
	
		if (Cli::$instance) {
			echo "Cli session already exists\n";
			return;
		}
		new Cli();
		$socket = stream_socket_server("tcp://".Cli::$host.":".Cli::$port,$errno,$errstr);		
		stream_set_blocking($socket,0);
		$stdin = fopen("php://stdin","r+");				
		stream_set_blocking($stdin,0);				
		if (!$socket) {
			BH_util::log("--> error: could not establish host [$errstr ($errno)]");
		} else {
			BH_util::log("CLI host established");		
			while (true) {	
			
				//echo "loop\n";
				
				// check for user input (safe: will be buffered)
				if ($query = trim(fgets(STDIN))) {
					if (strtolower($query) == "exit") {
						BH_util::log("CLI host shutting down");
						return;
					}
					BH_util::log("==");											
					BH_util::log("CLI RECEIVE [$query]");										
					BH_util::log("==");							
					Interpreter::receive("000:cli",$query);													
				}

				// check for outgoing sockets
				if ($connection = @stream_socket_accept($socket,1)) {
					echo "\n>> ";	// w/o this echo, sometimes our response returns nothing, even if it should. inexplicably, this fixes it.
					while ($response = fread($connection,1024)) {					
						BH_util::log("==");							
						BH_util::log("CLI SEND [$response]");		
						BH_util::log("==");							
						fputs(STDOUT,$response."\n");			
					}
					fclose($connection);
				}			

			}
		}
		
	}
	
	
	private function __construct () {

		BH_util::log("CLI created");	
		Cli::$instance = $this;
	
	}


	public function send ($query) {
	
		BH_util::log("CLI: sending [$query]");
		if (!$connection = @fsockopen(Cli::$host,Cli::$port,$errno,$errstr,30)) {
			BH_util::log("--> error: connecting to host [$errstr ($errno)]");
			return;	
		}	
		if (!fputs($connection,$query)) {
			BH_util::log("--> error: sending content");
		} else {
			BH_util::log("--> success");
		}
		fclose($connection);	
	
	}
	

}

?>