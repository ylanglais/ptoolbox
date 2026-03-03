<?php

require_once("lib/dbg_tools.php");
require_once("lib/prov.php");

$p = new prov("db", "default.infra.host");

$d = $p->get(["osfamily" => "LINUX", "ison" => true], 0, 0, "name");

if (is_string($d)) $d = json_decode($d);

foreach ($d as $h) {
	if ($h->lastping == null || $h->type == 'Appliance') {
		continue;
	}
	$c = @fsockopen($h->name, 4445);
	if (is_resource($c)) print("$h->name\n");
	#else dbg("$h->name doesn't");
}
