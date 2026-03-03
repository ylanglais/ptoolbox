<?php
require_once("lib/date_util.php");
class test {
	static function ping() {
		return ["ok", [ "tstamp" => dbstamp() ]];
	}
	static function add(int $a, int $b) {
		dbg("test::add($a, $b)");
		return [ "ok", [ "add" => ($a + $b) ] ];
	} 
	static function count(array $a) {
		dbg("test::count(".json_encode($a).")");
		return [ "ok", [ "count" => count($a) ] ];
	} 
}
