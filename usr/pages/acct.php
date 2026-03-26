<?php

chdir("../..");
require_once("lib/dbg_tools.php");
require_once("lib/session.php");
require_once("usr/lib/acct.php");


acct_ctrl();

/*
global $_session_;
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
if ($_session_->isnew()) {
	print(acct_connect());
	return;
}

$roles = get_roles();

if ($roles == [] || $roles = ['local']) {
	$a = new args();
	if ($a->has("login") && $a->has("passwd")) {
		$u = new user();
		if (!($u->auth_check($a->val("login"), $a->val("passwd"), $a->val("ip")))) {
			html_err("invalid pair login/passwd");
			exit;
		}
		$_session_->create($u);
		$a->clean("passwd");
		$a->clean("login");
		dbg("user id: " . get_user_id());
		$roles = get_roles();
	}
}

if (in_array("Entreprise", $roles)) {
	print(acct_detail_pro());
	return;
} 
print(acct_detail_par());
*/
