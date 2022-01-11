<?php

$direct = false;
if (!file_exists("lib")) {
	chdir("..");
	$direct = true;
}

require_once("lib/session.php");
require_once("lib/args.php");

if ($direct == true) {
	global $_session_;
	#
	# Start/Restore session:
	if (!isset($_session_)) $_session_ = new session();
	#
	# Check 
	$_session_->check();

	$a = new args();
	if (!$a->has("reload")) return;
	print(layout_part_content($a->val("reload")));
	exit;
}


	
function layout_part_content($part) {
	$str = "";
	$fi  = "parts/$part.php";
	$fn  = $part. "_content";
	if (!file_exists($fi)) return $str;
	include($fi);
	if (function_exists($fn)) $str .= $fn();
	return $str;
}

function layout_part($divid, $divclass, $part) {
	$str = "<div id='$divid' class='$divclass'>";
	$str .= layout_part_content($part);
	$str .= "</div>\n";
	return $str;
}

print("<div id='body'>\n");
print(layout_part("heading", "heading", "heading"));
print(layout_part("menu", "menu", "menu"));
print("<div id='data_area'></div>\n</div>\n");
