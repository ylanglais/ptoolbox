<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/store.php");
require_once("lib/prov_db.php");
require_once("lib/prov_entity.php");

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
			if (preg_match("/^__(prov_[^_]*)_(.*)$/", $d, $m)) {
			$pv = $m[1];
			$this->prov = new $pv($d, null, $filter);
			} else {
				$pv = "prov_".$d;
				$this->prov = new $pv($datalink, null, $filter);
			}
		}
	}

	function type() {
		if ($this->prov == null) return null;
		return $this->prov->type;	
	}

	function datalink() {
		if ($this->prov == null) return null;
		return $this->prov->datalink;
	}

	function count() {
		if ($this->prov == null) return null;
		return $this->prov->count();
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
	function defval($data) {
		if ($this->prov == null) return null;
		return $this->prov->defval($data);
	}
	function quote($data) {
		if ($this->prov == null) return null;
		return $this->prov->quote($data);
	}
	function get($data) {
		if ($this->prov == null) return null;
		return $this->prov->get($data);
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
	function query($start = 0, $limit = 25, $sortby = false, $order = false) {
		if ($this->prov == null) return null;
		return $this->prov->query($start, $limit, $sortby, $order);
	}
}
?>
