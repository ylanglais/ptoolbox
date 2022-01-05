<?php
require_once("lib/session.php");
require_once("lib/args.php");
require_once("lib/date_util.php");
require_once("lib/dbg_tools.php");

global $s;
if (!isset($s)) $s = new session();
#
# Make sure we are connected:
$s->check();

$a = new args();

if (!$a->has("module")) {
	dbg_err("No action specified");
 	return;
}
$module = $a->val("module");
if ($a->has("titre")) {
	$titre = $a->val("titre");
} else {
	$titre = $module;
}
include($module);



?>
