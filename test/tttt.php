<?php

require_once("lib/dbg_tools.php");
require_once("lib/ssg.php");
require_once("lib/query.php");

function list_espp_logs($srv, $path) {
	$o = ssg($srv, "zcat $path/access.*gz | md5sum");
	if (count($o) == 0) return false;
	foreach ($o as $l) {
		if (preg_match("#^([^ ]*) [0-9]* (.*)$#", $l, $m)) {
			yield (object) [ "file" => $m[2], "cksum" => $m[1] ];
		} 
		$p = ssg($srv, "md5sum $path/access.*gz");
	}
}

