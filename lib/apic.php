<?php 
require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class apic {
	function __construct($opts = false) {
		$flds = [ 'url', 'login', 'passwd', 'noproxy', 'debug' ];
		$conf = "conf/apic.php";
		if ($opts === false || !is_array($opts)) {
			if (!file_exists($conf)) {
				err("no apic configuration file");
				return;
			}
			include($conf);
			foreach ($flds as $f) {
				if (isset(${"apic_$f"})) $this->$f = ${"apic_$f"};
				else $this->$f = false;
			}
		} else {
			foreach ($flds as $f) {
				if (array_key_exists($f, $opts)) $this->$f = $opts[$f];
				else $this->$f = false;
			}	
		} 
		if ($this->url == false) {
			err("no url given");
			return;
		}
		$this->hdr = [ 'accept: application/json', 'content-type: application/json' ];
		$this->_connect();
	}
	
	function __destruct() {
		$c = new curl($this->url, $this->hdr, $this->noproxy, $this->debug);
		if ($this->url === false) return;
		$c->post("logout");
	}

	function _connect() {
		if (($c = new curl($this->url, $this->hdr, $this->noproxy, $this->debug)) === false) {
			$this->url = false;
			err("connot connect");
			return;
		}
		if (($r = trim($c->post("login", '{ "login":"'. $this->login . '", "passwd":"'. $this->passwd .'"}' ))) === false) {
			$this->url = false;
			err("cannot connect to api");
			return;
		}	
		if (($v = json_decode(trim($r))) === false ) {
			$this->url = false;
			err("bad reply");
			return;
		}
		if (is_array($v)) {
			$token = $v["data"]["token"];
		} else if (is_object($v) && property_exists($v, "data") && is_object($v->data) && property_exists($v->data, "token")) { 
				$token = $v->data->token;
		} else {
			$this->url = false;
			err("no token provided, here is the full reply: $r\n");	
			return;
		}
		array_push($this->hdr, "Authorization: Bearer $token");
	}

	function call($action, $data = false) {
		if ($this->url === false) {
			err("not connected");
			return false;
		}
		if (is_array($data) || is_object($data)) {
			$data = json_encode($data);
		}
		if (($c = new curl($this->url, $this->hdr, $this->noproxy, $this->debug)) === false) {
			err("connot connect");
			return false;
		}
		$r = $c->post($action, $data);
		if ($r === false) return false;
		$r = trim($r);
		$o = json_decode($r);
		if ($o === false || !is_object($o) || !property_exists($o, "msg")) {
			err("bad return $r");
			return false;
		}
		if (!property_exists($o, "msg") || $o->msg != "ok") {
			if (!property_exists($o, "reason")) 
				err("returned $o->msg");
			else
				err("returned $o->msg with reason: $o->reason");
			return false;
		}
		return $o->data;
	}

	function test() {
		$r = $this->call("ping");
		if (is_object($r) && property_exists($r, "tstamp")) return true;
		return false;
	}

	function ping() {
		$r = $this->call("ping");
		if (is_object($r) && property_exists($r, "tstamp")) 
			return $r->tstamp;
		return false;
	}
}

