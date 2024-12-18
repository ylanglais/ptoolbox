<?php

require_once("lib/curl.php");
require_once("lib/dbg_tools.php");

class veeam {
	function __construct() {
		$file = "conf/veeam.php";
		$this->auth_dbg = false;
		$this->curl_dbg = false;


		if (!file_exists("$file")) {
			err("no config file ($file)");
			return;
		}
		include($file);
		$this->base = $veeam_base;
		$this->apiv = $veeam_apiv;

		if (isset($veeam_auth_dbg)) $this->auth_dbg = $veeam_auth_dbg;
		if (isset($veeam_curl_dbg)) $this->curl_dbg = $veeam_curl_dbg;

		$c = new curl($veeam_base, ["accept: application/json", "Content-Type: application/x-www-form-urlencoded"], false, $this->auth_dbg);

		$user = urlencode($veeam_user);
		$pass = urlencode($veeam_pass);



		$r = $c->post("token", "username=$user&password=$pass&grant_type=password&refresh_token=");
		if ($this->auth_dbg) dbg($r);
		if ($r === false || ($d = json_decode($r)) === false) return;
		if (!property_exists($d, "access_token")) return;
		$this->auth =  "Authorization: $d->token_type $d->access_token";
		$this->hdr  =  [ $this->auth, "Accept: application/json" ];
	}
	function get($endpoint, $param = []) {
		if (!isset($this->auth)) {
			err("veeam driver not properly initialized");
			return false;
		}
		$c = new curl($this->base, $this->hdr, false, $this->curl_dbg);
		$r = $c->get("/$this->apiv/$endpoint", $param);
		if ($r === false) return false;
		return json_decode($r);		

	}

	function all_page_get($what, $filter = "", $sort = "", $select = "") {
		$p = 0;
		$n = 100;
		$d = [];

		while ($r = $this->get($what, [ "offset" => $p, "limit" => $n, "filter" => $filter, "sort" => $sort, "select" => $select] )) {
			#dbg($r);
			if (!is_object($r)) break;
			if (!property_exists($r, "items")) {
				err("invalid data returned:");
				err($r);
				return $d;
			} 
			$d = array_merge($d, $r->items);
			$p++;
			if ($r->totalCount < $n) break;
		}
		return $d;
	}

	function page_get($what, $offset = 0, $limit = 100, $filter = "", $sort = "", $select = "") {
		return $this->get($what, [ "offset" => $offset, "limit" => $limit, "filter" => $filter, "sort" => $sort, "select" => $select] );
	}

	function data_get($what, $param = []) {
		return $this->get($what, $param);
	}
	
	function about() {
		return $this->get("about");
	} 
	function templates($offset = 0, $limit = 100) {
		return $this->get("alarms/templates", ["offset" => 0, "limit" => $limit ]);
	}
	function sessions($offset = 0, $limit = 100) {
		return $this->get("sessions", ["offset" => 0, "limit" => $limit ]);
	}
	function backupservers($offset = 0, $limit = 100) {
		return $this->get("vbr/backupServers", ["offset" => 0, "limit" => $limit ]);
	}
	function backupagents($offset = 0, $limit = 100) {
		return $this->get("vbr/backupAgents", ["offset" => 0, "limit" => $limit ]);
	}
	function vmjobs($offset = 0, $limit = 100) {
		return $this->get("vbrJobs/vmBackUpJobs", ["offset" => 0, "limit" => $limit ]);
	}
}
