<?php 

require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");
require_once("conf/api.php");

function mthd_args($cls, $mthd) {
$r = new ReflectionMethod($cls, $mthd);
$al = [];
foreach ($r->getParameters() as $a) {
	if (preg_match('/[ 	]*Parameter #([0-9]*) \[ <(required|optional)> (([^ ]*) )?\$([a-zA-Z0-9_]*) (= ([^ ]*) )?\]/', $a, $m)) {
		$o = (object)[];
		#foreach ($m as $i => $v) print("$i: $v\n");
		$o->name = $m[5];
		$o->num  = $m[1];
		$o->opt  = true;  if ($m[2] == "required") $o->opt  = false;
		$o->type = [] ;   if ($m[4] != "")         $o->type = explode("|", $m[4]);
		$o->hdef = false; if (count($m) >= 7)      $o->hdef = true;
		$o->def  = null;  if ($o->hdef)            $o->def  = $m[7];
		array_push($al, $o);
	} else  {
		print("no match $a\n");
	}
}
return $al;
} 

function get_token() {
$h = null;
if (isset($_SERVER['Authorization'])) {
	$h = trim($_SERVER["Authorization"]);
} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
	$h = trim($_SERVER["HTTP_AUTHORIZATION"]);
} elseif (function_exists('apache_request_headers')) {
	$requestHeaders = apache_request_headers();
	$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
	if (isset($requestHeaders['Authorization'])) {
		$h = trim($requestHeaders['Authorization']);
	}
}
if ($h == null) return false;
if (strstr($h, "Bearer ") !== false) return substr($h, 7);

return false;
}

global $_session_;

function sip_get() {
if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
	$sip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$sip = $_SERVER['REMOTE_ADDR'];
}
return $sip;
}

function api_notfound($reason = "") {
_err("not found $reason");
header('HTTP/1.0 404 Not found');
if ($reason == "") {
	print('{"msg": "Not found"}');
} else {
	print('{"msg": "Not found", "reason": "'.$reason.'"}');
}	
exit;
}

function api_auth_required($reason = "") {
header("HTTP/1.0 511 Network Authentication Required");
if ($reason == "") {
	print('{"msg": "Network Authentication Required"}');
} else {
	print('{"msg": "Network Authentication Required", "reason": "'.$reason.'"}');
}
exit;
}

function api_forbiden($reason = "") {
_err("forbiden $reason");
header('HTTP/1.0 403 Forbidden');
if ($reason == "") {
	print('{"msg": Forbidden"}');
} else {
	print('{"msg": "Forbidden", "reason": "'.$reason.'"}');
}	
exit;
}
function api_reply($msg, $data = '', $token = false) {
if ($msg != "welcome" && $msg != "ok" && $msg != "goodby") {
	#_err("msg: $msg ($data)");
	print('{"msg": "'.$msg.'", "reason": "' . $data . '"}');
	exit;
} 
if ($token !== false) header("Authorization: Bearer $token");
#header('Content-type: application/json');
print('{"msg": "'.$msg.'", "data": ' . $data . '}');
exit;
}
#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
$a = new args();


$token = get_token(); 
#
# Check 
if ($_session_->isnew() || $token == false) {
#
# Check sourceIP:
$sip = sip_get();
if ($api_filter_ip === true) {
	$q = new query("select hostname from api_ip where ip = '$sip'");
	if ($q->nrows() != 1)  {
		_err('api connexion attempts from non authorized ip : '.$sip);
		api_forbiden("unauthorized ip");
	}
}

#
# Check request IS login:
if ($a->has('req') === false || $a->val('req') != 'login') {
	_err('api connexion request attempted without beeing logged ('. $a->val('req').') from '. $sip);
	api_forbiden("not logged");
}
#
# Check post data contains login & passwd:
if ($a->post('login') === false || $a->post('passwd') === false) {
	_err('api connexion attempts with missing parameters from ' . $sip);
	api_forbiden("missing parameters");
}

$u = new user();

#
# Check passwd:
if (!($u->auth_check($a->post("login"), $a->post("passwd"),  $_SERVER['REMOTE_ADDR']))) {
	_err('api connexion attempts with bad login/passwd pair '.$a->post('login').' from ' . $_SERVER['REMOTE_ADDR']);
	api_forbiden("bad login/passwd");
}

#
# check if user is authorized to use api:
if (!$u->has_role("api")) {
	_err('api connexion attempts with login'.$a->post('login').' which has no api role from ' . $_SERVER['REMOTE_ADDR']);
	api_forbiden("no api role");
}

$tok = (object)[];

#$tok->usr = $a->post("login");
#$tok->rol = $u->roles();
$tok->sip = $sip;
$tok->exp = datetime_shift("+" . $api_expiration_minutes . " minutes");
 
$token = openssl_encrypt(json_encode($tok), $api_method, $api_key, 0, $api_iv);


$_session_->create($u);
$a->clean("passwd");
$a->clean("login");
#
#
api_reply("welcome", '{"token":"'.$token.'"}', $token);
} else {
#
# Check token:
$tok = json_decode(openssl_decrypt($token, $api_method, $api_key, 0, $api_iv));
if (!is_object($tok) || !property_exists($tok, "sip") || !property_exists($tok, "exp") /*|| !property_exists($tok, "usr") || !property_exists($tok, "rol")*/) {
	$_session_->destroy();
	api_auth_required("bad token");
}

if ($tok->sip != sip_get()) {
	$_session_->destroy();
	api_auth_required("inconsistent ip");
} 
if (time() > $tok->exp) {
	$_session_->destroy();
	api_auth_required("session expired");
}
}
if ($a->has('req') === false) api_notfound("no request passed");
$req = $a->val("req");
$action = ""; 
if ($a->has('action')) $action = $a->val('action');

switch ($req) {
case "login": 
api_reply('goodby');
break;

case "logout":
$_session_->destroy();
api_reply('goodby');
break;

case "ping":
api_reply("ok", '{ "tstamp": "'. timestamp() .'" }');
break;

case "probe":
if ($a->post("WHAT") === false)             api_reply("Nothing to probe");
if ($a->post("DTYP") === false)             api_reply("No dtyp to probe");
if ($a->post("DATA") === false)             api_reply("No data to probe");

$what = $a->post("WHAT");
$dtyp = $a->post("DTYP");
$data = $a->post("DATA");

if (!file_exists("usr/$what.php"))          api_reply("ko", "$what doesn't exist");
if (!(include("usr/$what.php")))            api_reply("ko", "$what is an inconsistent probe"); 

$probe = new $what();

$d = null;
if ($data != null) {
	$d = json_decode($data);
	if ($d == null) {
		#file_put_contents("tmp/api.malformed.data", $data);
		api_reply("ko", "malformed data");
	}
}
if (!method_exists($probe, $dtyp)) 	api_reply("ko", "$what probe has not method called $dtyp");

$r = $probe->$dtyp($d);

api_reply("ok", json_encode($r));
break;

default:
if (file_exists("api/$req.php")) {
	include("api/$req.php");
	if (method_exists($req, $action)) {
		$l = mthd_args($req, $action);
		$alst = [];
		foreach ($l as $o) {
			if ($a->has($o->name)) {
				$v = $a->val($o->name);
				if ($o->type != []) {
					$ok = false;
					foreach ($o->type as $t) {
						if ($t == "mixed") {
							$ok = true; break;
						}
						$chk = "is_". $t;
						#dbg("$t --> $chk($v) --> ". $chk($v) ? "true": "false");
						if (function_exists($chk) && $chk($v)) {
							$ok = true; break;	
						}
					}
					if ($ok == false) {
						api_reply("ko", "bad request to $req::$action, parameter $o->name should be in " . json_encode($o->type) . " but is " . gettype($v) );
					}
				} 	
				array_push($alst, $v);
			} else if ($o->opt == false) {
				api_reply("ko", "bad request to $req::$action, missing $o->name parameter");
			}
		}
		try {
			$rr = $req::$action(...$alst);
		} catch (TypeError | Exception $e) {
			api_reply("ko", $e->getMessage());
			} 
			api_reply(...$rr);
		} else {
			api_notfount("bad request $req::$action");
		}
	} else {
		api_notfound("bad request ($req)");
	}
}

?>
