<?php
require_once("lib/query.php");



function _schema() {
	static $audit_schema = null;
	if ($audit_schema != null) return $audit_schema;
	if (file_exists("conf/audit.php")) include("conf/audit.php");
	if ($audit_schema == null) $audit_schema = "";
	return $audit_schema;	
}

/** 
 * function to log login errors
 */
function audit_login_error($login, $ip, $error) {
	$t  = _schema() . "connection";
	new query("insert into $t values ('', '$login', now(), null, '$ip', '$error')");
}

/** 
 * function to log new logins 
 */
function audit_login($sid, $login, $ip) {
	$t  = _schema() . "connection";
	new query("insert into $t values ('$sid', '$login', now(), null, '$ip', 'login')");
}

/** 
 * function to log explicit logouts 
 */
function audit_logout($sid) {
	$t  = _schema() . "connection";
	new query("update $t set until=now(), State='logout' where id = '$sid' and State = 'login' and since = (select max(since) from $t where id = '$sid' and State = 'login')");
}
?> 
