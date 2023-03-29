<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class GMap {
	function __construct() {
		include("conf/ganalytics.php");
		$this->url = "https://maps.googleapis.com/maps/api/";
		$this->key = $gmap_key;
	}
	function query($str) {
		$c = new curl($this->url);
		if (!($r = $c->get("place/textsearch/json", [ "query" => "$str", "key" => $this->key ]))) 
			return false;
		if (!($rr = json_decode($r))) 
			return false;
		if (!(property_exists($rr, "status") || $r->status == "OK")) {
			err("bad return"); 
			return false;
		}
		return $rr->results;
	}
	function place($id, $fields = null) {
		$c = new curl($this->url);
		if ($fields != null) 
		$r = $c->get("place/details/json", [ "placeid" => "$id", "fields" => "$fields", "key" => $this->key ]);
		if (!$r) return false;
		if (!($rr = json_decode($r))) 
			return false;
		if (!(property_exists($rr, "status") || $rr->status == "OK")) {
			err("bad return"); 
			return false;
		}
		return $rr->result;
	}
}


?>
