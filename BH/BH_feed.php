<?php

include_once(dirname(__FILE__)."/magpierss/rss_fetch.inc");
//define('MAGPIE_CACHE_DIR','/tmp/magpie_cache');
define('MAGPIE_CACHE_ON',false);

class BH_feed {

	public static function get ($url) { return fetch_rss($url); }

}


?>