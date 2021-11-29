<?php
require_once("lib/auth_local.php");

$a  = new auth_local();

array_shift($argv);

foreach ($argv as $p) {
	print("$p --> " . $a->dbstr_from_pass($p));
}
?>
