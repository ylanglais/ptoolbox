<?php
require_once("lib/query.php");
require_once("lib/util.php");
require_once("lib/session.php");
require_once("lib/util.php");

function _schema() {
	static $audit_schema = null;
	if ($audit_schema != null) return $audit_schema;
	if (file_exists("conf/audit.php")) include("conf/audit.php");
	if ($audit_schema == null) $audit_schema = "";
	return $audit_schema;	
}

/** 
 * function to log login errors
 * @param $login 	login of the user
 * @param $ip		the incoming ip of the user
 * @param $error	the error message
 */
function audit_login_error($login, $ip, $error) {
	$t  = _schema() . "connection";
	new query("insert into $t values (:empty, :login, now(), null, :ip, :error)", [
		":empty" => "",
		":login" => $login,
		":ip"    => $ip,
		":error" => $error
	]);
}

/** 
 * function to log new logins 
 * @param $sid		the session id
 * @param $login 	login of the user
 * @param $ip		the incoming ip of the user
 */
function audit_login($sid, $login, $ip) {
	$t  = _schema() . "connection";
	new query("insert into $t values (:sid, :login, now(), null, :ip, 'login')", [
		":sid"   => $sid,
		":login" => $login,
		":ip"    => $ip
	]);
}

/** 
 * function to log explicit logouts 
 * @param $sid		the session id
 */
function audit_logout($sid) {
	$t  = _schema() . "connection";
	new query("update $t set until=now(), state='logout' where id = :sid and state = 'login' and since = (select max(since) from $t where id = :sid and state = 'login')", [
		":sid" => $sid
	]);
}
/** 
 * function to log message to table audit.log
 * @param $msg		the message to log
 */

function audit_log($level, $msg) {
	$ip  = get_ip();
	if ($ip === false) $ip = 'no ip';
	$usr = get_user();
	new query("insert into audit.log values (to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'), :ip,  :usr, :level, :msg)", [
		":ip"    => $ip,
		":usr"   => $usr,
		":level" => $level,
		":msg"   => $msg
	]); 
}
function audi_action($entiy, $entityid, $version, $action, $comment) {
/*
	new query("insert into audit.actions values (to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'), :ip,  :usr, :level, :msg)", [
		":ip"    => $ip,
		":usr"   => $usr,
		":level" => $level,
		":msg"   => $msg
	]); 
*/
}
?> 
