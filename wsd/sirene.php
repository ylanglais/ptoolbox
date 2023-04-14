<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class sirene {

	function __construct() {
		$this->bearer = false;
		if (!file_exists("conf/sirene.php")) {
			_err("no config file");
			return;
		}
		include("conf/sirene.php");

		$this->base  = $sirene_base;
		$this->tokn  = $sirene_tokn;
		$this->api3  = $sirene_api3;
		$this->bauth = "Authorization: Basic " .  base64_encode("$sirene_key:$sirene_sec");
		$this->hdr1  = "Content-Type: application/x-www-form-urlencoded"; 
		$this->hdr2  = "Accept: application/json";
		$c = new curl ($sirene_base, [ $this->hdr1, $this->bauth ], false, false);	
		$r = $c->post($this->tokn, "grant_type=client_credentials");
		if ($r !== false) {
			if (($j = json_decode($r)) !== false) {
				if (property_exists($j, "access_token")) {
					$this->bearer = $j->access_token;
					_dbg("bearer: $this->bearer"); 
				} else {
					_err("bad return : " . print_r($j, true));
				}
			} else {
				_err("invalid JSON: " . print_r($r, true)); 	
			}
		} else {
			_err("no return");
		}
	}

	function siren($string) {
		if ($this->bearer === false) {
			_err("no connexion to sirene");
			return false;
		}
		$c = new curl($this->base, [ $this->hdr1, $this->hdr2, "Authorization: Bearer $this->bearer" ], false, false);	
		$r = $c->post($this->api3 . "/siren", "q=$string"); 
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
