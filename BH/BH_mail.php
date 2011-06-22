<?php

include_once("phpmailer/class.phpmailer.php");
include_once("phpmailer/class.smtp.php");

class BH_mail {


	private static $instance;  // singleton
	private $mbox = null;
	private $params;
	private $verbose = false;


	static function open () {
	
		if (!isset(self::$instance)) {
			self::$instance = new BH_mail();
		}
		return self::$instance;
	
	}
	

	private function __construct () {
	
		$this->params = BH_config::get("email");
		$this->verbose =  isset($this->params['verbose']) && $this->params['verbose'] == "true" ? true : false;			
		if (isset($this->params['inbox'])) {		
			if ($this->connect() && $this->verbose) BH_util::log("BH: inbox opened");
		}
		
	}

	private function __clone () { }

	public function __destruct () { 
	
		if ($this->mbox) imap_close($this->mbox); 
		if ($this->verbose) BH_util::log("BH: inbox closed");		
		
	}
	
	
	private function connect () {
	
		if ( !($this->mbox != null && imap_ping($this->mbox)) ) {
			if ($this->verbose) BH_util::log("BH: attempting connection [".$this->params['inbox']."]");
			$this->mbox = @imap_open('{'.$this->params['inbox'].':143/imap/notls}INBOX',$this->params['username'],$this->params['password']);
			imap_errors();
			if (!$this->mbox) {
				BH_util::log("-----mail error: cant connect to inbox");
				return false;
			}
			return true;
		}
		return true;
	
	}
	

	public function getEmail () {
	
		$messages = array();

		if (!$this->connect()) return $messages;
	
		for ($num=1; $num<=imap_num_msg($this->mbox); $num++) {
																
			$header = imap_header($this->mbox,$num);
			if ($header->Deleted == 'D') continue;
					
			$message = array();
			$message['mailbox'] = $header->to[0]->mailbox."@".$header->to[0]->host;			
			$message['address'] = $header->from[0]->mailbox."@".$header->from[0]->host;
			$message['subject'] = isset($header->subject) ? $header->subject : null;
						
			$text = imap_fetchbody($this->mbox,$num,1); // part id is offset	
			if ( !(strpos($text,"------=_Part") === false) ) {
				$text = explode("------=_Part",$text);
				$text = explode("\n",$text[1]);
				$text = $text[4];
			}
			$message['text'] = trim($text);
			if (isset($this->params['keep_quotes']) && $this->params['keep_quotes'] == "false") {
				$message['text'] = $this->removeQuoted($message['text']);
			}
	
			$image = null;
			$audio = null;
			$struct = imap_fetchstructure($this->mbox,$num); // get messages structure
			if (isset($struct->parts)) {
				for ($part=0; $part<sizeof($struct->parts); $part++) {
					if ($struct->parts[$part]->type == 5 && $struct->parts[$part]->subtype == "JPEG") {
						$image = imap_base64(imap_fetchbody($this->mbox,$num,$part+1)); // part id is offset	
						break; // take the first one					
					}
					if ($struct->parts[$part]->type == 4 && $struct->parts[$part]->subtype == "WAV") {
						$audio = imap_base64(imap_fetchbody($this->mbox,$num,$part+1)); // part id is offset
						break; // take the first one					
					}					
				}
			}
			$message['image'] = $image;
			$message['audio'] = $audio;
		
			$messages[] = $message;
			if ($this->verbose) BH_util::log("BH: received email from [".$message['address']."] subject [".$message['subject']."] content [".substr($message['text'],0,100)."...] image [".($message['image'] ? "true" : "false")."] audio [".($message['audio'] ? "true" : "false")."]");
			imap_delete($this->mbox,$num);
	
		}
						
		if ($num > 1) imap_expunge($this->mbox);
		return $messages;
		
	}
	
	
	public function sendEmail ($to,$subject,$text) {

		$args = func_get_args();
		if (isset($args[3])) $html = $args[3]; else $html = null;
		if (isset($args[4])) $attachment = $args[4]; else $attachment = null;
		if (isset($args[5])) $content_type = $args[5]; else $content_type = "image/jpeg";
		if (isset($args[6])) $from = $args[6]; else $from = $this->params['from'];

		$mailer = new PHPMailer();
		$mailer->isSMTP();
		$mailer->SMTPAuth = true;
		$mailer->Username = $this->params['username'];
		$mailer->Password = $this->params['password'];
		$mailer->Host = $this->params['host'] . ":" . $this->params['port'];
		$mailer->From = $from;
		$mailer->FromName = $from;
		$mailer->AddAddress($to);
		$mailer->Subject = $subject ? $subject : "";
		$mailer->Body = $text;
		if ($html) {
			$mailer->Body = $html;
			$mailer->AltBody = $text;
		} else {
			$mailer->Body = $text;
		}
		
		if ($attachment) $mailer->addAttachment($attachment,$attachment,"base64",$content_type);

		if ($this->verbose) BH_util::log("BH: attempting to send email...");
		if (!$mailer->send()) {
			BH_util::log("-----mail error [".$mailer->ErrorInfo."] to [$to] from [$from] subject [$subject] text [$text]".($attachment ? " with attachment [$content_type]" : ""));
			return false;
		} else {
			if ($this->verbose) BH_util::log("BH: sent email to [$to] from [$from] content [$text]".($attachment ? " with attachment [$content_type]" : ""));
			return true;
		}

	}	
	
	
	function removeQuoted ($string) {
	
		$pattern = '/-+ *Original Message *-+/i';		// case insensitive
		preg_match($pattern,$string,$result,PREG_OFFSET_CAPTURE);
		if (sizeof($result)) {
			$string = substr($string,0,$result[0][1]);
		}

		$pattern = '/-+ *Forwarded *-+/i';		// case insensitive
		preg_match($pattern,$string,$result,PREG_OFFSET_CAPTURE);
		if (sizeof($result)) {
			$string = substr($string,0,$result[0][1]);
		}
	
		$string = BH_util::linenormalize($string);
		$string = str_replace("\n","f*z(",$string);
			
		$pattern = '/On .*wrote:/';		// case sensitive
		preg_match($pattern,$string,$result,PREG_OFFSET_CAPTURE);
		if (sizeof($result)) {
			$string = substr($string,0,$result[0][1]);
		}
		
		$pattern = '/([-a-zA-Z0-9_.]+)@(([-a-zA-Z0-9_]+[.])+[a-zA-Z]+) .*wrote:/';   // too risky? ok for TXTML, but maybe not otherwise
		preg_match($pattern,$string,$result,PREG_OFFSET_CAPTURE);
		if (sizeof($result)) {
			$string = substr($string,0,$result[0][1]);
		}		
		
		$string = str_replace("f*z(","\n",$string);
		return $string;
	
	}
	
	
	static function poll5 ($function) {
	
		$time = 298;
		while ($time > 0) {
		
			BH_util::stopwatch("poll");
			$mailer = BH_mail::open();
		
			foreach ($mailer->getEmail() as $email) $function($email);
			
			sleep(2);
			$time -= BH_util::stopwatch("poll");
		
		}
	
	}


}

?>