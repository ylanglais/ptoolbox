<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class probe {
	function __construct() {
		include("conf/probe.php");	
		$this->baseurl   = $probe_baseurl;
		$this->noproxy   = $probe_bypass_pxy;
		$this->debug     = false;
		if (isset($probe_debug)) $this->debug = $probe_debug;
		$this->user      = $probe_login;
		$this->pass      = $probe_passwd;
		$this->redir     = false;
		$this->header    = [ "Content-type: application/json" ];
		#if (substr($probe_baseurl, -3) == "php") {
		#	$this->redir = false;
		#}
		$this->connected = false;
		$this->connect();
	}

	function _post($what, $data) {
		$d = $data;
		if (!is_string($data)) $d = json_encode($data);
		$cu = new curl($this->baseurl, $this->header, $this->noproxy, $this->debug);
		return $cu->post($what, $d);
	}

	function connect() {
		$r = $this->_post("", json_encode(['req' => 'login', 'login' => $this->user, 'passwd' => $this->pass]));
		$res = json_decode($r);
		if ($res == null)     return;
		if (!is_object($res)) return;
		if (!property_exists($res, "msg") || !property_exists($res, "data")) return;
		if (!property_exists($res->data, "token")) return;
		array_push($this->header, "Authorization: Bearer " . $res->data->token);
		$this->connected = true;
	}

	function __destruct() {
		if ($this->redir) {
			$r = $this->_post("logout", []);
		} else {
			$r = $this->_post("", ['req' => 'logout']);
		}
	}

	function _send($what, $dtyp, $data) {
			$r = $this->_post("", ['req' => 'probe', 'WHAT' => $what, 'DTYP' => $dtyp, 'DATA' => $data ]);

		if ($r === false) {
			err("probe::send() false returned");
			return false;
		} else {
			$rr = json_decode($r);
			if ($rr == null) {
				err("cannot decode json:\n$r");
				return false;
			}
			return $rr;
		}
	}
	function send($what, $dtyp, $data) {
		$r = $this->_send($what, $dtyp, $data);
		if ($r == false) return false; 
		if ($r->msg != 'ok') {
			if (property_exists($r, "reason")) {
				if ($r->reason == "not logged" && $this->connected) {
					$this->connect();
					$r = $this->_send($what, $dtyp, $data);
				}
			}
		}
		return $r;
	}

	function check($r, $what = "", $dir = "send") {
		if ($r == null || $r === false) {
			err("cannot $dir $what data");
			return false;	
		} 
		if ($r->msg != "ok") {
			$str = "Error saving traffic : $r->msg";
			if (property_exists($r, "reason")) {
				$str .= " (reason: $r->reason)";
			}
			err($str);
			return false;
		}
		
		dbg("$what operation ok");
		return true;
	}
}

?>
