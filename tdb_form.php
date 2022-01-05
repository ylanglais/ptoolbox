<?php
require_once("lib/session.php");
require_once("lib/args.php");
require_once("lib/date_util.php");
require_once("lib/dbg_tools.php");

global $_session_;
if (!isset($_session_)) $_session_ = new session();
#
# Make sure we are connected:
$_session_->check();

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
