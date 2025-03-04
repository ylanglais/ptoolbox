<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class cw {
	function __construct() {
		$file = "conf/cw.php";
		if (!file_exists($file)) {
			err("no config file ($file)");
			return;
		}
		include($file);
		$this->url = $cw_url;
		$tokn = base64_encode("$cw_key:$cw_secret");

		$this->header = [ "Content-Type: application/json",    "Authorization: Basic $tokn" ];
	}

	function get($endpoint, $param = []) {
		$c = new curl($this->url, $this->header, false, false);
		$r = $c->get("/$endpoint", $param);
		if ($r === false) return false;
		return json_decode($r);		
	}

	function agents() {
		$p = 1;	
		$a = [];
		while ($b = $this->get("agents", [ "page" => $p, "per_page" => 100 ])) {
			if (!is_array($b) || $b == []) break;
			
			$p++;
			$a = array_merge($a, $b);
			if (count($b) < 100) break;
		}
		return $a;
	}

	function servers() {
		$p = 1;	
		$s = [];
		while ($b = $this->get("servers", [ "page" => $p, "per_page" => 100 ])) {
			if (!is_array($b) || $b == []) break;
			
			$p++;
			$s = array_merge($s, $b);
			if (count($b) < 100) break;
		}
		return $s;
	}

	function server($id) {
		return $this->get("assets/servers/$id");
	}
}

