<?php
class test {
	static function add(int $a, int $b) {
		dbg("test::add($a, $b)");
		return [ "ok", [ "add" => ($a + $b) ] ];
	} 
	static function count(array $a) {
		dbg("test::count(".json_encode($a).")");
		return [ "ok", [ "count" => count($a) ] ];
	} 
}
