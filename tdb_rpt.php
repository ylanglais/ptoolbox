<?php
require_once("lib/rpt.php");
require_once("lib/session.php");
require_once("lib/args.php");
require_once("lib/dbg_tools.php");
require_once("lib/stats.php");

global $_session_;
if (!isset($_session_)) $_session_ = new session();
#
# Make sure we are connected:
$_session_->check();


$a = new args();

if (!$a->has("rptname")) {
	err("No rptname specified");
 	return;
}
#if ($a->has("titre")) {
	#$titre = $a->val("titre");
#} else {
	#$titre = $rptname;
#}
$rptname = $a->val("rptname");

$j = file_get_contents($rptname);
$js = json_decode($j);
if ($js === false) {
	err("invalid json");
	return;
}
$r = new rpt($js);
$st = hrtime(true);
$h = $r->parse($js);
$et = hrtime(true);
stats_update($rptname, (($et - $st) / 1e9));
print($h."\n");

