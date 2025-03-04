<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/store.php");
require_once("lib/prov_db.php");
require_once("lib/prov_view.php");
require_once("lib/util.php");

class prov { 
	function __construct($d, $datalink = null, $filter = null) {
		$this->type = null;
		$this->prov = null;
		if (is_array($d)) $d = (object) $d;

		if (is_object($d) && property_exists($d, "prov") && 
			is_object($d->prov) && property_exists($d->prov, "type")) {
			$this->type = $d->prov->type;
			$pv = "prov_".$d->prov->type;
			$this->prov = new $pv($d, $datalink, $filter);
		} else if (is_string($d)) {
			$d = unb64($d);
			if (preg_match("/^__(prov_[^_]*)_(.*)$/", $d, $m)) {
				$pv = $m[1];
				$this->prov = new $pv($d, null, $filter);
			} else {
				$pv = "prov_".$d;
				$this->prov = new $pv($datalink, null, $filter);
			}
		}
	}
	function id() {
		if ($this->prov == null) return null;
		return $this->prov->id;	
	}
	function type() {
		if ($this->prov == null) return null;
		return $this->prov->type;	
	}
	function datalink() {
		if ($this->prov == null) return null;
		return $this->prov->datalink;
	}
	function perm() {
		if ($this->prov == null) return null;
		return $this->prov->perm();	
	}
	function count($filter = null) {
		if ($this->prov == null) return null;
		return $this->prov->count($filter);
	}
	function data() {
		if ($this->prov == null) return null;
		return $this->prov->data();
	}
	function fields() {
		if ($this->prov == null) return null;
		return $this->prov->fields();
	}
	function keys() {
		if ($this->prov == null) return null;
		return $this->prov->keys();
	}
	function name() {
		if ($this->prov == null) return null;
		return $this->prov->name();
	}
	function iskey($data) {
		if ($this->prov == null) return null;
		return $this->prov->iskey($data);
	}
	function has_fk($data) {
		if ($this->prov == null) return null;
		return $this->prov->has_fk($data);
	}
	function datatype($data) {
		if ($this->prov == null) return null;
		return $this->prov->datatype($data);
	}
	function defval($data) {
		if ($this->prov == null) return null;
		return $this->prov->defval($data);
	}
	function nullable($data) {
		if ($this->prov == null) return null;
		return $this->prov->nullable($data);
	}
	function quote($field, $val) {
		if ($this->prov == null) return null;
		return $this->prov->quote($field, $val);
	}
	function get($data, $limit = 0, $start = 0, $sortby = false, $order = false) {
		if ($this->prov == null) return null;
		return $this->prov->get($data, $limit, $start, $sortby, $order);
	}
	function del($data) {
		if ($this->prov == null) return null;
		return $this->prov->del($data);
	}
	function put($data) {
		if ($this->prov == null) return null;
		return $this->prov->put($data);
	}
	function update($data) {
		if ($this->prov == null) return null;
		return $this->prov->update($data);
	}
	function filter($filter = null) {
		if ($this->prov == null) return null;
		return $this->prov->filter($filter);
	}
	function query($start = 0, $limit = 25, $sortby = false, $order = false, $filter = null) {
		if ($this->prov == null) return null;
		return $this->prov->query($start, $limit, $sortby, $order, $filter);
	}
	function fdata($field, $str = false, $max = 20) {
		if ($this->prov == null) return null;
		return $this->prov->fdata($field, $str, $max);
	}
}

function prov_ctrl() {
	$a = new args();
	if ($a->has("prov_data") && $a->has("action")) {
		$action = $a->val("action");
		if ($action == "fdata") {
			$pdata = $a->val("prov_data");
			if (!$a->val("field")) return "";
			$field = $a->val("field");
			$str = false;
			if ($a->has("str")) {
				$str = $a->val("str");
			}
			$p = new prov($pdata);
			return json_encode($p->fdata($field, $str));
		}
	}
	return "";
}
?>
