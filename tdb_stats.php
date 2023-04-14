<?php
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

if (!$a->has("stats_key")) {
	err("No stats_key pecified");
 	return;
}
$key = $a->val("stats_key");
print(json_encode(stats_get($key)));
?>
