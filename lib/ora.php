<?php

require_once("lib/query.php");
require_once("lib/dbg_tools.php");

if (function_exists("oci_connect")) {
class ora {
	function __construct($db_dsn = false, $db_user = false, $db_pass = false) {
		$this->oci = false;
		if ($db_dsn === false || $db_dsn == "dbs=default" || $db_dsn == "default") {
			if (file_exists("conf/ora.php")) {
				include("conf/ora.php");
				$db_dsn = "$ora_tns";
				if ($db_user === false && isset($ora_user)) $db_user = $ora_user;
				if ($db_pass === false && isset($ora_pass)) $db_pass = $ora_pass;

				if (preg_match("/\(SERVICE_NAME=([^)]*)\)/", $ora_tns, $m)) {
					$this->db_name = $m[1];
					$this->tns     = $m[1];
				} else if (preg_matfch("|^[^:]*:[^:]*/(.*)$|", $ora_tns, $m)) {
					$this->db_name = $m[1];
					$this->tns     = $m[1];	
				} else $this->db_name = $ora_tns;
			} else return;
		//} else if (preg_match("/^(([^:]*):)?(([^(]*)|\(.*\(SERVICE_NAME=([^)]*)\).*\))$/", $db_dsn, $m)) {
		} else if (preg_match("/^\(.*\(SERVICE_NAME=([^)]*)\).*\)$/", $db_dsn, $m)) {
			$this->tns     = $m[1];
			$this->db_name = $m[3];
		} else if (preg_match("|^[^:]*:[^:]*/(.*)$|", $db_dsn, $m)) {
			$this->db_name = $m[1];
			$this->tns     = $m[1];
		} else if (file_exists($db_dsn)) {
			if (substr($db_dsn, -4) == ".php") {
				include($db_dsn);
				if (!isset($ora_tns)) return;
				$db_dsn  = "$ora_tns";
				if ($db_user === false && isset($ora_user)) $db_user = $ora_user;
				if ($db_pass === false && isset($ora_pass)) $db_pass = $ora_pass;
			} else if (substr($db_dsn, -5) == ".json") {

				$js = json_decode(file_get_contents($db_dsn));
				if ($js == false || !property_exists($js, "ora_tns") || !property_exists($js, "ora_user") || !property_exists($js, "ora_user")) return;
				
				$db_dsn  = "$js->ora_tns";
				$db_user = $js->ora_user;
				$db_pass = $js->ora_pass;
			} else return;
			
		}  else {
			if (preg_match("/^dbs=(.*)$/", $db_dsn, $m))
				$dbs = $m[1];
			else 
				$dbs = $db_dsn;

			$q = new query("select drv, host, port, name, \"user\", pass from tech.dbs where dbs = '$dbs'");
			if ($q === false) {
				$db_dsn  = $dbs;
			} else {
				$o = $q->obj();
				foreach (["drv", "host", "port", "name", "user", "pass"] as $k) {
					$this->{"db_".$k} = $o->$k;
				}	
				$db_user = $this->db_user;
				$db_pass = $this->db_pass;
				$db_dsn  = $this->db_name;
			}
		}
#dbg("$db_user, $db_pass, $db_dsn");
		if (($this->oci = oci_connect($db_user, $db_pass, $db_dsn, "UTF8")) === false) {
			_err("Cannot connect to db using $db_dsn: " . oci_errorInfo());
			$this->oci = false;
			return;
		}	
	}
	function oci() {
		return $this->oci;
	}
	function err() {
		return oci_error();
	}
	
}
class oqry {
	function __construct($ora, $query) {
		$this->err = false;	
		$this->qry = false;	
		$this->ora = $ora;
		$oci = 	$ora->oci();
		if ($oci === false) return;
		if (($this->qry = oci_parse($ora->oci(), $query)) === false) {
			err("Cannot proces query $query: " . oci_error() . "\n");
			$this->err = oci_error();
			$this->qry = false;
			return;
		}
		if (($status = oci_execute($this->qry, OCI_COMMIT_ON_SUCCESS)) === false) {
			err("Error executing query $query: " . oci_error() . "\n");
			$this->err = oci_error();
			$this->qry = false;
			return;
		}
	}
	function err() {
		return $this->err;
	}
	function obj() {
		if ($this->qry === false) return false;
		return oci_fetch_object($this->qry);
	}
	function data() {
		if ($this->qry === false) return false;
		return oci_fetch_array($this->qry);
	}
	function all() {
		if ($this->qry === false) return false;
		$a = [];
		while ($o = $this->obj()) {
			array_push($a, $o);
		}
		return $a; 
	}
	function all_data() {
		if ($this->qry === false) return false;
		$a = [];
		while ($o = $this->data()) {
			array_push($a, $o);
		}
		return $a; 
	}
	function csv($sep = ";", $del = "") {
		$all = "";
		while ($o = $this->obj()) {
			$r = [];
			foreach ($o as $k => $v) { array_push($r, $del.$v.$del); }
			$all .= implode($sep, $r) . "\n";
		}
		return $all;
	}
	function json() {
		$all = [];
		$all = $this->all();
		return json_encode($all);
	}
	function now() {
		$s = 'SELECT SYSTIMESTAMP as now FROM dual';

		$q = new oqry($q);

		if (($req = $this->query($qry)) == null) {
			return null;
		}
		$dat = $this->data();
		return $dat['now'];
	}
}
}
