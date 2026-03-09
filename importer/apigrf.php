<?php
require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class apigrf {
	private $token;
	private $hdr;
	private $ignore_proxy = false;
	function __construct() {
		$this->token = false;	
		$this->hdr = false;	
		$this->hdr = false;	
		if (!file_exists("Configs/apigrf.conf")) {
			_err("no Configs/apigrf.conf");
			return;
		}
		include("Configs/apigrf.conf");
		if (isset($apigrf_ignore_proxy)) $this->ignore_proxy = $apigrf_ignore_proxy;
		$this->base  = $apigrf_base;
		$this->token = $this->_login($apigrf_user, $apigrf_pass);
		if ($this->token != false) {$r;
			$this->hdr = ["Content-Type: application/json; charset=utf-8", "Authorization: Bearer $this->token" ];
		} else {
			_err("no token found");
		}
	}
	private function _login($user, $pass) {
		$c = new curl($this->base, ["Content-Type: application/json; charset=utf-8"], $this->ignore_proxy, false);
		$r = $this->_result($c->post("auth/login", '{"user": "'.$user.'", "password": "'.$pass.'"}'));
		if (!is_object($r) || !property_exists($r, "token")) {
			_err("malformed response :" . print_r($r, true));
			return false;
		}
		return $r->token;
	}
	private function _result($raw) {
		if ($raw === false || (($r = json_decode($raw)) === false)) {
			#_err("bad return >>> ". print_r($raw, true)); 
			return false;
		} 
		if (!is_object($r)) {
			_err("malformed response :" . print_r($r, true));
			return false;
		} 
		if (property_exists($r, "status")) {
			if ($r->status != 1) {
				_err("bad status: " . ( $r->status === false ? "false" : $r->status ));
				return false;
			}
		}
		return $r;
	}
	function get($endpoint, $param = null) {
		if (is_array($param) || is_object($param)) $param = json_encode($param);
		$c = new curl($this->base, $this->hdr, $this->ignore_proxy, false);
		$r = $c->send("get", $endpoint, $param);
		if ($r === false) return false;
		return $this->_result($r);		
	}
	function post($endpoint, $param = null) {
		if (is_array($param) || is_object($param)) $param = json_encode($param);
		$c = new curl($this->base, $this->hdr, $this->ignore_proxy, false);
		$r = $c->send("post", $endpoint, $param);
		if ($r === false) return false;
		return $this->_result($r);		
	}
	function put($endpoint, $param = null) {
		if (is_array($param) || is_object($param)) $param = json_encode($param);
		$c = new curl($this->base, $this->hdr, $this->ignore_proxy, false);
		$r = $c->send("put", $endpoint, $param);
		if ($r === false) return false;
		return $this->_result($r);		
	}
	function del($endpoint, $param = null) {
		if (is_array($param) || is_object($param)) $param = json_encode($param);
		$c = new curl($this->base, $this->hdr, $this->ignore_proxy, false);
		$r = $c->send("delete", $endpoint, $param);
		if ($r === false) return false;
		return $this->_result($r);		
	}
	function entity_send($json) {
		return $this->post("entite/send", $json);
	}

	function cv_send($json) {
		return $this->post("cv/send", $json);
	}
	
}




