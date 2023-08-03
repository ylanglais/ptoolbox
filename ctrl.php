<?php

require_once("lib/args.php");
require_once("lib/dbg_tools.php");
require_once("lib/session.php");

global $_session_;
#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
#
# Check 
$_session_->check();
#
$a = new args();
if ($a->has("ctrl")) {
	$ctrl = $a->val("ctrl");
	#dbg("ctrl = $ctrl");
	$f = "lib/$ctrl.php";
	if (!file_exists($f)) {
		$f = "usr/lib/$ctrl.php";	
		if (!file_exists($f)) {
			err("$f does not exist");
			exit();
		}
	}
	include($f);
	$fct = $ctrl . "_ctrl";
	if (!function_exists($fct)) {
		err("$f has no $fct");
		exit();
	}
	print($fct());
} else {
	err("no ctrl found");
}

