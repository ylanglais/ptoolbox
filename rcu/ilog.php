<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/util.php");

class ilog {
	private $_id;
	private $_flx;
	private $_odb;

	function __construct($id, $flux, $pid = null, $file = null, $cmd = "") {
		$this->_id   = false;
		$this->_odb  = false;
		$this->_flx  = false;

		$conf = "conf/logimport.php";

		if (!file_exists($conf)) {
			_err("$conf");
			return;
		}
		include("$conf");

		$this->_odb = new db($logimport_dsn, $logimport_user, $logimport_pass);
		if (($e = $this->_odb->error()) !== false) {
			_err("cannot connect to db");
			return;
		}

		$this->_id = $id;
		
		if ($id == null) { 
			$this->_id = $id = gen_uuid();

			$v = [];

			foreach (["id", "flux", "pid", "file", "cmd"] as $k) {
				if ($$k  == "" || $$k == null) {
					array_push($v, "null");
				} else {
					array_push($v, "'".$$k."'");
				}
			}
			$sql = "insert into tech.log_import (id, flux, pid, file, cmd, status) values (" . implode(",", $v) .", 'Crée')";
			#_info("sql = '$sql'");
			$q = new query($sql, $this->_odb);
			if (($r = $q->error()) !== false) {
				_err($r);
			}
		} 
	}

	function reject($file, $ref, $reason) {
		$set = [];

		$id      = gen_uuid();
		$id_flux = $this->_id;
		$flux    = $this->_flx;

		foreach ([ "id", "id_flux", "flux", "file", "ref", "reason"] as $k) {
			if ($$k  == "" || $$k == null) {
				array_push($set, "null");
			} else {
				array_push($set, "'".esc($$k)."'");
			}
		}
		$sql = "insert into tech.rejects (id, id_flux, flux, file, reference, reason) values (".implode(",", $set).")";
		$q = new query($sql, $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
		}
	}

	function get($fields) {
		if ($this->_id === false) {
			_err("no id");
			return null;
		}
		if ($this->_odb === false) {
			_err("not connected");
			return null;
		}
		$q = new query("select * from tech.log_import where id = '$this->_id'", $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return null;	
		}
		if (($o = $q->obj()) === false) {
			_err("id $this->_id not found");
			return null;
		}
		if (!is_array($fields)) {
			if (!property_exists($o, $fields)) {
				_err("property $fields doesn't exist");
				return null;
			}
			return $o->$fields;
		}
		$r = [];

		foreach ($fields as $f) {
			if (property_exists($o, $f)) {
				$r[$f] = $o->$f;
			}
		}
		return $r;
	}

	function set($fields, $val = null) {
		$s = "update tech.log_import set ";
		$w = " where id = '$this->_id'";

		if (! is_array($fields)) {
			if ($val == null) return false;
			$sql = $s . " $fields = $val " . $w;
		} else {
			if ($val != null) _warn("value is not null, ignored");	
			$set = [];
			foreach ($fields as $k => $v) {
				if ($v == "" || $v == null) {
					array_push($set, "$k = null");
				} else {
					array_push($set, "$k = '$v'");
				}
			}	
			$sql = $s . implode(",", $set) . $w;
		}
				
		$q = new query($sql, $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return null;	
		}
	}

	function status($status = null) {
		$init = $this->get("status");
		if ($status == null) {
			return $init;
		}

		if ($this->_id === false) {
			_err("no pid");
			return null;
		}
		if ($this->_odb === false) {
			_err("not connected");
			return null;
		}
		$s = "update tech.log_import set status = '$status' where id = '$this->_id'";

		$q = new query($s, $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return null;	
		}
		return $init;
	}

	function is_running() {
		$r = $this->get([ "pid", "cmd" ]);
		if (!is_array($r) || !array_key_exists("pid", $r)) return false;
		$pid = $r["pid"];

		if ($pid == null) return false;
		if (!file_exists("/proc/$pid/cmdline")) return false;

		if (!array_key_exists("cmd", $r) || $r["cmd"] == null) return true;

		$cmd = str_replace(" ", "", $r["cmd"]);

		$c = file_get_contents("/proc/$pid/cmdline");
		if ($c != $cmd) {
			_info(">>> $c vs $cmd");
			return false; 
		}
		return true;
	}

	function terminate($status, $set_end_date = true) {
		if ($this->_id === false) {
			_err("no pid");
			return false;
		}
		if ($this->_odb === false) {
			_err("not connected");
			return false;
		}
		$s = "update tech.log_import set status = '$status'";

		if ($set_end_date) $s .= ", end_date = CURRENT_TIMESTAMP";

		$q = new query($s, $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return false;	
		}
		return true;
	}

	function dbnow() {
		if ($this->_odb === false) {
			_err("not connected");
			return null;
		}
		$q = new GQuery("select current_timestamp as now",  $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return null;	
		}
		return $q->obj()->now;
	}

	function id() {
		return $this->_id;
	}

	function elapsed() {
		if ($this->_odb === false) {
			_err("not connected");
			return null;
		}
		if ($this->_id === false) {
			_err("no id");
			return null;
		}
		$q = new query("select (case when end_date is null then (current_timestamp - start_date) else (end_date - start_date) end) as delta from tech.log_import where id= '$this->_id'", $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
			return null;	
		}
		return $q->obj()->delta; 
	}
}

