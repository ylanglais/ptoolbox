<?php
require_once("lib/curl.php");

class zabbix {
	function __construct() {
		$token = false;	
		$this->init = false;
		$cfile = "conf/zabbix.php";
		if (!file_exists($cfile)) {
			_err("no config file ($cfile)");
			return;
		}
		include($cfile);
		foreach ([ "url", "script", "tokn"] as $v) {
			if (!isset(${"zabbix_$v"})) {
				err("No $v defined in zabbix config file");
				return;
			}
			$this->{$v} = ${"zabbix_$v"};
		}
		#$this->jrpcv = $zabbix_ver;
		$this->hdr   = [ "Content-Type: application/json-rpc; charset=utf-8" ];
		#$this->user = $zabbix_user;
		#$this->pass = $zabbix_pass;
		if (isset($zabbix_tokn)) {
			$this->tokn  = $zabbix_tokn;
			array_push($this->hdr, "Authorization: Bearer $this->tokn");
			$this->init = true;
		}
	}

	function _post($method, $data) {
		$a = '{"jsonrpc": "2.0","method": "' . $method .
			 '","params":'.json_encode($data).
			 ',"id": 2}';
		$c = new curl($this->url, $this->hdr, false, false);
		return $c->post($this->script, $a);
	}	

}

$z = new zabbix();
print($z->_post("apiinfo.version", []). "\n");
print($z->_post("alert.get", [ "output" => "extend", "actionids" => 3 ]) . "\n");


?>
