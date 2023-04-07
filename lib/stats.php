<?php
require_once("lib/query.php");
require_once("lib/dbg_tools.php");


function stats_get($key) {
	$q = new query("select * from stats.duration where key = '$key'");
	return $q->obj();
}

function stats_update($key, $val) {
	$o = stats_get($key);
	if ($o === false) {
		dbg(">> insert into stats.duration values ('$key', 1, $val, $val, $val)");
		$q = new query("insert into stats.duration values ('$key', 1, $val, $val, $val)");
	} else {
		if ($val < $o->min) $o->min = $val;
		if ($val > $o->max) $o->max = $val;
		$o->avg = ($n * $o->avg + $val) / ($n + 1);
		$o->n++;
		dbg(">> update stats.duration set n = $o->n, min = $o->min, max = $o->max, avg = $o->avg where key = '$key'");
		new query("update stats.duration set n = $o->n, min = $o->min, max = $o->max, avg = $o->avg where key = '$key'");
	}
}
?>
