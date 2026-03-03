<?php

require_once("lib/ora.php");
require_once("lib/dbg_tools.php");

$db = new ora("creprod");
$q  = new oqry($db, "select * from CRE_SICARE.CRE_PERSONNE where PRS_ID = '968230' ");
$a = 0;
while ($o = $q->obj()) {
	if ($a == 0) {
		$t = [];
		foreach ($o as $k => $v) {
			array_push($t, $k);
		}
		$a++; 
	}
	$t = [];
	foreach ($o as $k => $v) {
		array_push($t, $v);
	}
	print(implode(";", $t) . "\n");
}
