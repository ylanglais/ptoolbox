<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class sirene {

	function __construct() {
		if (!file_exists("conf/sirene.php")) {
			_err("no config file");
			return;
		}
		include("conf/sirene.php");

		$this->base  = $sirene_base;
		$this->tokn  = $sirene_tokn;
		$this->api3  = $sirene_api3;
		$this->cert  = $sirene_cert;
		$this->ctyp  = $sirene_ctype;
		$this->cpas  = $sirene_cpass;
		$this->ckey  = $sirene_ckey;
		$this->hdr1  = "Accept: application/json";
		$this->hdr2  = "X-INSEE-Api-Key-Integration: $sirene_key";
	}

	function siren($string) {
		$c = new curl($this->base, [ $this->hdr1, $this->hdr2 ], false, false);	
		$c->certificate($this->cert, $this->ctyp, $this->ckey, $this->cpas); 
		$r = $c->get($this->api3 . "/siren?q=$string"); 
		if ($r !== false) {
			if (($j = json_decode($r)) !== false) {
				if (property_exists($j, "header") && property_exists($j->header, "message")) {
					if ($j->header->message == "OK") {
						return $j;
					} else {
						_err("status " . $j->header->statut . ", message: " . $j->header->message);
					}
				}
			} else {
				_err("invalid JSON: " . print_r($r, true));
			}
		} else {
			_err("no return");
		}
		return false;
	}
}

?>
