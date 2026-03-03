<?php

require_once("lib/query.php");
require_once("lib/prov.php");

$p = new prov("db", "default.ttt");

$p->put(["id" => 6, "tof" => true, "name" => "toutou"] );

$a = $p->get(["tof" => "true"]);
foreach ($a as $o) {
	dbg($o);
}
$tt = (object) ["ori" => [ "name" => "toutou" , "id" => 6, "tof" => "true"], "data" => ["name" => "touti", "tof" => "false"]];
dbg($tt);
$p->update($tt);
$a = $p->get(["tof" => "true"]);
foreach ($a as $o) {
	dbg($o);
}
$p->del(["id" =>6]);
