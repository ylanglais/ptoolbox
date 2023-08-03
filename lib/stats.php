<?php
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/args.php");

function stats_get($key) {
	$q = new query("select * from stats.duration where key = '$key'");
	return $q->obj();
}

function stats_update($key, $val) {
	$o = stats_get($key);
	if ($o === false) {
		$q = new query("insert into stats.duration values ('$key', 1, $val, $val, $val)");
	} else {
		if ($val < $o->min) $o->min = $val;
		if ($val > $o->max) $o->max = $val;
		$o->avg = ($o->n * $o->avg + $val) / ($o->n + 1);
		$o->n++;
		new query("update stats.duration set n = $o->n, min = $o->min, max = $o->max, avg = $o->avg where key = '$key'");
	}
}

function stats_ctrl() {
	$a = new args();

	if (!$a->has("stats_key")) {
		err("No stats_key pecified");
		return;
	}
	$key = $a->val("stats_key");
	print(json_encode(stats_get($key)));
}

?>
