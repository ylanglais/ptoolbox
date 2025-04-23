<?php
require_once("query.php");
class rflowerr {
	static function maxstamp() {
		$q = new query("select max(tstamp) as tstamp from rflow_errors");
		$o = $q->obj();
		return [ "ok", ["stamp" => $o->tstamp ] ];
	}
	static function insert(array $data) {
		return [ "ok", [ "status" => "inserted"] ];
	}
}
