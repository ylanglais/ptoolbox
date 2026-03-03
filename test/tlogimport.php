<?php
require_once("lib/dbg_tools.php");
require_once("lib/logimp.php");

$li = new logimp("test");
$li->import("logimp.tst.log");

?>
