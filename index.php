<?php

require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");
require_once("lib/style.php");

# Get POST data:
$args = new args();

global $_session_;

# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
# Check 
if ($_session_->isnew()) {
	if (!$args->has("login") || !$args->has("passwd")) {
		header("Location: login.php");
		exit;
	} else {
		$u = new user();
		if (!($u->auth_check($args->val("login"), $args->val("passwd"), $args->val("ip")))) {
			html_err("invalid pair login/passwd");
			header("Location: login.php");
			exit;
		}
		$_session_->create($u);
		$args->clean("passwd");
		$args->clean("login");
	}
} else if ($_session_->page() == "logout") {
	$_session_->destroy();
	$args->all_clean();
	header("Location: login.php");
	exit;
}
style::reload();
include("parts/header.php");
include("parts/layout.php");
include("parts/tailer.php");	
?>
