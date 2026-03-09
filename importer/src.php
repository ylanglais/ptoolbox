<?php

require_once("rcu/src_csv.php");
require_once("rcu/src_db.php");
require_once("rcu/src_ws.php");

class src {
	static function validate($conf) {
		$greq = [ "medium", "type", "srcname", "name", "key", "params" ];
	
		$errs = [];

		foreach ($greq as $g) 
			if (!property_exists($conf, $g)) array_push($errs, "parameter $g is missing");

		if (property_exists($conf, "medium") && !in_array($conf->medium,[ "ws", "db", "csv", "json"])) 
			array_push($errs, "$conf->medium is not a known medium");

		if (property_exists($conf, "type") && !in_array($conf->type, ["main", "ref"])) 
			array_push($errs, "$conf->type is not a known type");
		
		$cname = "src_" . $conf->medium;
		if (($r = $cname::validate($conf)) != true)
			return array_merge($errs, $r);
		return $errs == [] ? true : $errs;
	}

	function __construct($conf) {
		$this->srcname = $conf->name;
		$cname         = "src_" . $conf->medium;
		$this->src     = new $cname($conf);
	}
	function key_ref() {
		return $this->src->key_ref();
	}
	function nlines() {
		return $this->src->nlines();	
	}
	function next() {
		return $this->src->next();	
	}
	function srcname() {
		return $this->src->srcname();	
	}
	function value($field) {
		return $this->src->value($field);	
	}
	function key_value() {
		return $this->src->key_value();
	}
	function ref($value, $field) {
		return $this->src->ref($value, $field);
	}


}
