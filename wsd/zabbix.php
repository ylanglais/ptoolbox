<?php
require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

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
		#$this->user = $zabbix_user;
		#$this->pass = $zabbix_pass;
		$this->hdr   = [ "Content-Type: application/json-rpc; charset=utf-8" ];
		if (isset($zabbix_tokn)) {
			$this->tokn  = $zabbix_tokn;
			array_push($this->hdr, "Authorization: Bearer $this->tokn");
			$this->init = true;
		}

	}
	function _post($method, $data) {
		$a = '{"jsonrpc": "2.0","method": "' . $method .  '","params":'. json_encode($data).  ',"id": 2, "auth": "'.$this->tokn.'"}';
		#dbg($a);
		$c = new curl($this->url, $this->hdr, false, false);
		$r =  $c->post($this->script, $a);
		if ($r === false) return false;
		if (($r = json_decode($r)) === false) return false;
		if (!(property_exists($r, "result"))) {
			err(json_encode($r));
			return false;
		}
		return $r->result;
	}	
	function hostgroup_get($names) {
		return $this->_post("hostgroup.get", ["output" => ["groupid", "name"], "filter" => [ "name" => $names]]);
	}
	function host_by_grpid($grpid) {
		return $this->_post("host.get", ["groupids" => $grpid]);
	}
	function service_get($params = []) {
		return $this->_post("service.get", $params);
	}
	function problem_get($params = []) {
		return $this->_post("problem.get", $params);
	}
	function sla_get($params = []) {
		return $this->_post("sla.get", $params);
	}
	function alert_get($params = []) {
		return $this->_post("sla.get", $params);
	}
	
	function hostinterface_get($params = []) {
		return $this->_post("hostinterface.get", $params);
	}
	function report_get($params = []) {
		return $this->_post("report.get", $params);
	}
	
}

?>
