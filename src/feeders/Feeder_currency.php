<?php

class Feeder_currency extends Feeder {

	public function update (Feed $feed) {
	
		return; // disabled
				
		$values = array();
		$rss = BH_feed::get("http://currencysource.ez-cdn.com/USD.xml");
		if (!$rss) return;		
		foreach ($rss->items as $item) {
			$data = $item['title'];
			$data = str_replace("1 USD = ","",$data);
			$cc = BH_util::prefix(" ",$data);
			$value = BH_util::suffix(" ",$data);
			$value = str_replace("(","",$value);
			$value = str_replace(")","",$value);	
			$value = floatval($value);
			$values[$cc] = $value;
		}

		$feed->setVar("usd","1");		
		if (isset($values['EUR'])) $feed->setVar("euro",$values['EUR']);
		if (isset($values['GBP'])) $feed->setVar("pound",$values['GBP']);
		if (isset($values['CAD'])) $feed->setVar("cad",$values['CAD']);
		if (isset($values['JPY'])) $feed->setVar("yen",$values['JPY']);
		if (isset($values['INR'])) $feed->setVar("rupee",$values['INR']);
		if (isset($values['BRL'])) $feed->setVar("real",$values['BRL']);
		if (isset($values['CNY'])) $feed->setVar("yuan",$values['CNY']);		
		if (isset($values['EUR'])) $feed->setVar("last_update",strval(time()));		

	}

}

?>