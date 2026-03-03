<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("rcu/dbmeta.php");
require_once("rcu/src.php");
require_once("rcu/env.php");

class src_db {
	private $odb;
	private $dm;
	private $qry;
	private $cu = 0;

	function __destruct() {
		_dbg("cache usage: $this->cu");
	}

	static function validate($conf) {
		$errs = [];
		if (!property_exists($conf, "params")) 
			return "params parameter is missing";

		if (!property_exists($conf->params, "file")) 
			array_push($errs, "parameter file is missing");

		if ($conf->type == "ref") 
			if (!property_exists($conf, "refkey")) array_push($errs, "refkey parameter is missing");

		return $errs == [] ? true : $errs;
	}
	function __construct($conf) {
		$this->key = $conf->key;
		$this->obj = false;
		#
		# **IMPROVEMENT REQUIRED**	
		#
		$this->dm  = new dbmeta($conf->params->schema, $conf->params->table, (array) $conf->params);
		$this->odb = $this->dm->db_connection();

		$this->name    = $conf->name;
		$this->srcname = $conf->srcname;

		if ($conf->type == "main") {
			$this->type =  "main";
			$this->current = -1;
			$this->qry = new query($conf->params->query, $this->odb);
			if (($r = $this->qry->error()) !== false) {
				_err("invalid query: $r");
				$this->odb = false;
				return;
			}
			$this->nlines = $this->qry->nrows();
			_dbg("lines: $this->nlines");
		} else {
			$cols = [];
			$this->type = "ref";
			$this->key_ref  = $conf->refkey;
			$this->reftable = $conf->params->table;
			$this->cache[$this->reftable] = [];
		}
	}

	function srcname() {
		return $this->srcname;
	}
	function key_ref() {
		return $this->key_ref;
	}
	function nlines() {
		if ($this->type == "main")
			return $this->nlines;
		return false;
	}
	function next() {
		if ($this->type == "main" && $this->current < $this->nlines - 1) {
			$this->obj = $this->qry->obj();
			if ($this->obj == false) return false;
			$this->current++;
			return true;
		}
		return false;
	}
	function key_value() {
		if ($this->type == "main" && $this->obj !== false and property_exists($this->obj, $this->key))
			return $this->obj->{$this->key};
		return false;
	}
	function value($field) {
		if ($this->type == "main" && $this->obj != false && property_exists($this->obj, $field))
			return $this->obj->$field;
		return false;
	}
	function ref($kval, $field) {
		if ($this->type != "ref")  		 return false;
		#_dbg("---> select $field as val from $this->reftable where $this->key = '$kval'");

		if (array_key_exists($kval, $this->cache[$this->reftable]) &&  array_key_exists($field, $this->cache[$this->reftable][$kval])) {
			$this->cu++;
			return $this->cache[$this->reftable][$kval][$field];
		}

		$q = new query("select $field as val from $this->reftable where $this->key = '$kval'", $this->odb);
		if (($o = $q->obj()) === false)  return false;
		if (!property_exists($o, "val")) return false;

		if (count($this->cache[$this->reftable]) < 100) {
			if (!array_key_exists($kval, $this->cache[$this->reftable])) $this->cache[$this->reftable][$kval]= [];
			$this->cache[$this->reftable][$kval][$field] = $o->val;
		}	
		return $o->val;
	}
}
