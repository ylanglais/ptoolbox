<?php

require_once("lib/prov.php");

$p = new prov("db", "default.espp.files");
$r = $p->get([ "cksum" => "eeaf143caf4b07421cae8f257bea31ee" ]);
print(json_encode($r) . "\n");

$p = new prov("db", "default.param.folder_page");
$r = $p->get([ "folder_id" => 900 ]);
print(json_encode($r) . "\n");


?>
