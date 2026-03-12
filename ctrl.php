<?php

require_once("lib/args.php");
require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/session.php");

$a = new args();
if ($a->has("ctrl")) {
	$ctrl = $a->val("ctrl");
	#dbg("ctrl = $ctrl");
	$f = "usr/lib/$ctrl.php";
	if (!file_exists($f)) {
		$f = "lib/$ctrl.php";	
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
	//dbg("fonction: ". $fct);
	print($fct());
} else {
	err("no ctrl found");
}

