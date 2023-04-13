<?php
#
# file called by tdb_table
#
require_once("lib/args.php");
require_once("lib/dbg_tools.php");
require_once("lib/session.php");
require_once("lib/glist.php");
require_once("lib/prov.php");

global $_session_;
#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
#
# Check 
$_session_->check();
#
$a = new args();

if ($a->has("page")) $page = $a->val("page");
else $page = "Undefined";

print("<div id='gui_$page'>");
print("<h1>$page</h1>\n");

if (!$a->has("datalink")) {
	print("<h2>No datalink</h2></div>");
	exit();
}
$datalink = $a->val("datalink");
if ($a->has("type")) {
	$type = $a->val("type");
} else {
	$type = "db";
}
dbg("type = $type");

print("<table class='form'><tr><td>\n");
print(glist(new prov($type, "$datalink"), ['gform_id' => "gform_$page"]));
print("</td><td>\n");
print("<div id='gform_$page'></div>\n");
print("</td></tr></table>\n");
print("</div>\n");

?>
