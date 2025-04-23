<?php
require_once("lib/dbg_tools.php");
require_once("lib/curl.php");

function rdap($ip) {
	$c  = new curl("http://rdap.net/", [ "Content-type: application/rdap+json" ], false, false);
	$r = $c->get("ip/$ip");
	if ($r === false) {
		warn("no return / bad ip $ip");
		return false;
	}
dbg($r);
	return json_decode($r);
}
function iprep($ip) {
	$fdgd_user="WGE4I7WCHoBNNyCv";
	$fdgd_pass= "lq7d7ueZyQZvtP6X";
	$fdgd_url = "https://@api.fraudguard.io/ip/";
	$fdgd_opts= [
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
		CURLOPT_USERPWD        => "$fdgd_user:$fdgd_pass"
	];
	$c   = new curl($fdgd_url, [ "Content-type: application/json" ], false, false, $fdgd_opts);
	if (($r = $c->get("$ip")) === false) return false;
	else return json_decode($r);
}

if ($argc == 2 && is_file($argv[1])) {
	$f = fopen($argv[1], "r");
	while(! feof($f))  {
		$i = chop(fgets($f));
		if ($i == "") continue;
		$r = rdap($i);
		if ($r !== false) {
			if (!property_exists($r, "name")) print("$i: ". json_encode($r)."\n"); 
			else if (  !property_exists($r, "country"))
				print("$i: name: " . $r->name . "\n");
			else
				print("$i: name: " . $r->name . ", country: ". $r->country . "\n");
		} else {
			print("$i: bad ip/no data\n");
		}
	}
	fclose($f);
} else {
	for ($j = 1; $j < $argc; $j++) {
		$r = rdap($i = $argv[$j]);
	#	$t = iprep($i); 
		if ($r !== false) {
print("$i: ". json_encode($r)."\n"); 
			continue;
			if (!property_exists($r, "name")) print("$i: ". json_encode($r)."\n"); 
			else if (  !property_exists($r, "country"))
				print("$i: name: " . $r->name . "\n");
			else
				print("$i: name: " . $r->name . ", country: ". $r->country . "\n");

		} else {
			print("$i: bad ip/no data\n");
		}
	}
}
?>
