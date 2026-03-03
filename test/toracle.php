<?php
require_once("lib/db.php");
require_once("lib/query.php");

$db = new db("crerec-odbc");

$q = new query($db, "select distinct owner as schema FROM all_tables");
	
$sl =  [];
while ($o = $q->obj()) {
	array_push($sl, $o->SCHEMA);
}

foreach ($sl as $s) {
	print("$s: \n");

	$r = "SELECT TABLE_NAME as table_name from all_tables where Owner = '$s'";
	dbg($r);
	$q = new query($db, "$r");
	$tl = [];

	while ($o = $q->obj()) {
		print("\t$s.$o->TABLE_NAME\n");
	}
}
