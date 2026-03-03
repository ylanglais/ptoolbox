<?php

require_once("lib/csv.php");
require_once("lib/cachecsv.php");

require_once("rcu/src.php");
require_once("rcu/env.php");

class src_csv {
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

		$sep = ";"; $del = '"';
		foreach (["sep", "del"] as $p) 
			if (property_exists($conf->params, $p)) $$p = $conf->params->$p; 
		$this->file = $conf->params->file;
		if ($conf->type == "main") {
			$this->type = "main";
			$this->csv = new csv(rcu_process_dir() . "/" . $conf->params->file, $sep, $del);
			$this->current = -1;
		} else {
			$cols = [];
			$this->type = "ref";
			$this->key_ref = $conf->refkey;
			if (property_exists($conf->params, "columns")) $cols = $conf->params->columns;
			$this->csv = new cachecsv(rcu_process_dir() . "/" . $conf->params->file, $conf->key, $cols, $sep, $del);
		}
	}

	function key_ref() {
		return $this->key_ref;
	}
	function nlines() {
		if ($this->type == "main")
			return $this->csv->nlines();
		return false;
	}
	function next() {
		if ($this->type == "main" && $this->current < $this->csv->nlines() - 1) {
			$this->current++;
			return true;
		}
		return false;
	}
	function srcname() {
		return $this->file;
	}
	function value($field) {
		if ($this->type == "main")
			return $this->csv->get($this->current, $field);
		return false;
	}
	function key_value() {
		if ($this->type == "main")
			return $this->csv->get($this->current, $this->key);
		return false;
	}
	function ref($kval, $field) {
		if ($this->type == "ref")
			return $this->csv->get($kval, $field);
		return false;
	}
}
