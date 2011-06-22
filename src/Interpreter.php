<?php

require_once(dirname(__FILE__)."/../BH/BH.php");
BH_config::basedir(dirname(__FILE__)."/..");
BH_config::loadClass(BH_config::basedir()."/src/modules/Module.php");
BH_config::loadClass(BH_config::basedir()."/src/feeders/Feeder.php");
BH_config::loadClass(BH_config::basedir()."/src/formats/Format.php");
BH_config::loadClass(BH_config::basedir()."/src/requestors/Requestor.php");

require_once(BH_config::basedir()."/twilio.php");
require_once(BH_config::basedir()."/src/Object.php");
require_once(BH_config::basedir()."/src/State.php");
require_once(BH_config::basedir()."/src/Block.php");
require_once(BH_config::basedir()."/src/User.php");
require_once(BH_config::basedir()."/src/Feed.php");
require_once(BH_config::basedir()."/src/Language.php");
require_once(BH_config::basedir()."/src/Cli.php");


class Interpreter {

	
	static function receive ($address,$content) {

		$db = BH_db::open();
		$db->lock();	
		BH_util::log("================================");	
		BH_util::log("receive from [$address] content [$content]");		
		$user = Object::create("User",$address);
		self::log($user,0,$content,$content = Language::clean($content));						
		BH_util::log("cleaned content [$content]");				
		$time = time();		
		$keyword = BH_util::prefix(" ",$content);
		if ($keyword == "bhping") {
			BH_util::log("ping ping");
			self::send($user,"ping",0);
		} else if (in_array($keyword,array( "quit","stop","end","exit","cancel","unsubscribe" ))) {			
			BH_util::log("opt out");						
			if ($user->state() && $user->state()->quit()) {
				$user->setState($user->state()->quit());
			}
			$user->deactivate();
		} else if ($keyword == "help") {
			BH_util::log("help request");									
			if ($user->state() && $user->state()->help()) {
				$user->setState($user->state()->help());
			}
		} else {
			if ($user->state()) {
				$result = true;
				if ($user->state()->prefix()) {
					$result = $user->state()->prefix()->handle("input",$user,$time,$content);
				}
				if ($result !== false) $user->state()->handle("input",$user,$time,$content);
			} else {
				$starter = State::keyword($keyword);									
				if ($starter && $starter->start()) {
					BH_util::log("keyword [$keyword] routed to [".$starter->name()."]");
					$user->setState($starter);
				} else {
					$starter = State::default_state();
					if ($starter && $starter->start()) {
						BH_util::log("keyword [$keyword] routed to DEFAULT [".$starter->name()."]");
						$user->setVar("cta",$content);
						$user->setState($starter);	
					} else {
						BH_util::log("no routing for keyword [$keyword] and no default configured");
						$user->deactivate();									
					}
				}
			}
		}
		Object::saveState();		
		BH_util::log("================================");
		$db->unlock();		
	
	}
	

	static function send (User $user,$content) {
	
		static $n = 0;

		$content = substr(trim($content),0,160);	
		$content = BH_util::singlespace($content);
		$content = str_replace('"',"'",$content);
		$address = $user->address();
		if ($content == "") {
			BH_util::log("attempt to send blank message to [$address]");
			return;
		}
		BH_util::log("==");				
		BH_util::log("send to [$address] content [$content]");						
		if ($address == "cli") {
			Cli::send($content);
		} else {
			self::send_twilio($address,$content);
		}
		if (!isset($filepath)) $filepath = "-";
		self::log($user,1,$content,$content);
	
	}


	static function log (User $user,$push,$raw,$content) {
	
		$db = BH_db::open();
		$time = date("Y-m-d H:i:s");
		$statename = $user->state() ? "'".$user->state()->name()."'" : "null";
		$query = "INSERT INTO sms (address,send,time,content,raw,state,user_id) VALUES ('".$user->address()."','$push','$time','".$db->safe($content)."','".$db->safe($raw)."',$statename,".$user->id().")";
		$db->call($query);
	
	}
	
	
	static function timer () {

		$db = BH_db::open();
		$db->lock();	
		BH_util::log("================================");	
		BH_util::log("TIMER");
		$time = microtime(true);
		
		User::purge();
		
		BH_util::log("processing all user triggers...");
		foreach (User::loadWaiting() as $user) {
			$elapsed = microtime(true) - $time;
			if ( $elapsed >= 50 ) {
				BH_util::log("timer capacity reached ($elapsed), not processing any more",true);
				break;
			}
			if ($user->state()) {
				$result = true;
				if ($user->state()->prefix()) {
					$result = $user->state()->prefix()->handle("time",$user,$time,null);
				}
				if ($result !== false) $user->state()->handle("time",$user,$time,null);
			} else {
				BH_util::log("orphan user");
				$user->deactivate();
			}
		}

		Object::saveState();
		$elapsed = microtime(true) - $time;		
		BH_util::log("(".BH_util::shortdecimal($elapsed)."s elapsed)");
		BH_util::log("================================");		
		$db->unlock();				
	
	}
	
	
	static function api ($module,$user,$input,$parameters) {

		BH_util::log("================================");	
		BH_util::log("API: action [$module] user [$user] input [$input] parameters [".implode(" ",$parameters)."]");		
		$db = BH_db::open();
		$db->lock();
		if (!$user = Object::load("User",$user)) {
			BH_util::log("--> no user [$user]");				
			return;
		}		
		$class = "Module_".$module;
		if (!class_exists($class)) {
			BH_util::log("--> no module [$module]");				
			return;
		}
		$string = "<$module ";
		foreach ($parameters as $param => $value) $string .= "$param=\"".BH_util::xmlencode($value)."\" ";
		$string .= "/>";
		$txtml = simplexml_load_string($string);
		$module = new $class($txtml);
		$module->execute($user,time(),$input);
		Object::saveState();		
		$db->unlock();			
		BH_util::log("================================");		

	}
	
	static function send_twilio ($address,$content) {
		

		// twilio REST API version
		$ApiVersion = "2008-08-01";
		
		$account = BH_config::get("twilio");
		BH_util::log("Initializing Twilio...");

		// instantiate a new Twilio Rest Client
		$client = new TwilioRestClient($account['sid'], $account['token']);
		
		// Send a new outgoing SMS by POST'ing to the SMS resource */
		$response = $client->request("/$ApiVersion/Accounts/{$account['sid']}/SMS/Messages", 
			"POST", array(
			"To" => $address,
			"From" => $account['number'],
			"Body" => $content
		));
		if ($response->IsError) {
			BH_util::log("--> send failed! [".trim($response->ErrorMessage)."]");			
			BH_util::log("==");				
			return false;
		}
		BH_util::log("--> send success!");			
		BH_util::log("==");		
		return true;				
		
	}
	
}
		
?>