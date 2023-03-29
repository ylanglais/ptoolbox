<?php
require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class msdq {
	function __construct() {
		$token = false;	
		if (!file_exists("conf/msdq.php")) {
			_err("no conf/msdq.php");
			return;
		}
		include("conf/msdq.php");
		$this->base  = $msdq_url;
		$this->hdr = [ "Content-Type: application/json; charset=utf-8", "clef: $msdq_key", "imputation: $msdq_imputation" ];
	}
	private function _result($raw) {
		if ($raw === false || (($r = json_decode($raw)) === false)) {
			_err("bad return"); 
			return false;
		} 
		if (!is_object($r)) {
			_err("not an object");
			return false;
		}
		if (property_exists($r, "error")) {
			_err("error $r->error: $r->message");
			return false;
		}
		return $r;
	}
	private function _post($endpoint, $param = null) {
		if (is_array($param) || is_object($param)) $param = json_encode($param);
		$c = new curl($this->base, $this->hdr, false, false);
		$r = $c->send("post", $endpoint, $param);
		if ($r === false) return false;
		return $this->_result($r);		
	}
	function email_check($email, $opt = []) {
		if ($email == "") return false;
		$r = $this->_post("api/email/check", '{ "email_address": "' . $email . '" }');
		if ($r === false) return false;
		if (!property_exists($r, "email")) {
			_err("bad response");
			return false;
		}
		return (object) $r;
	}
	function email_normalize($email, $opt = []) {
		if ($email == "") return false;
		$r = $this->_post("api/email/normalize", '{ "email_address": "' . $email . '" }');
		if ($r === false) return false;
		if (!property_exists($r, "email")) {
			_err("bad response");
			return false;
		}
		return (object) $r;
	}
	function phone_normalize($phone, $country = "FR") {
		if ($phone == "") return false;
		$r = $this->_post("api/phone/normalize", '{ "number": "' . $phone. '", "country": "' . $country . '"}');
		if ($r === false) return false;
		return (object) $r;

	}
	function phonelist_normalize_str($phone) { // A SUPP MAIS UTLISEE POUR LE MOMENT
		if (is_object($phone) || is_array($phone) ) return false;
		$r = $this->_post("api/phonelist/normalize", $phone);
		if ($r === false) return false;
		return (object) $r;

	}
	function phonelist_normalize($phone) {
		if (is_object($phone) || ! is_array($phone) ) return false;
		$r = $this->_post("api/phonelist/normalize", json_encode(["items" => $phone ]));
		if ($r === false) return false;
		return (object) $r;

	}
	function address_normalize($address) {
		if (!is_object($address) || $address == (object) [] ) return false;
		$r = $this->_post("api/address/normalize", json_encode($address));
		if ($r === false) return false;
		return (object) $r;
	}

	function addresslist_normalize($address, $opt=[]) {
		if (is_object($address) || ! is_array($address) ) return false;
		$r = $this->_post("api/addresslist/normalize", json_encode([ "options" => $opt, "items" => $address ]));
		if ($r === false) return false;
		return (object) $r;
	}

	function visitcardlist_normalize($array) {
        $r = $this->_post("api/visitcardlist/normalize", json_encode($array));
        if ($r === false) return false;
        return (object) $r;
    }

	function dedup_preprocessing($data) {
		if (!is_object($data) || $data == (object) []) return false;
		$r = $this->_post("api/dedup/preprocessing", json_encode($data));
		if ($r === false) return false;
		return (object) $r;
	}
}
