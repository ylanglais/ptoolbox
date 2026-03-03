<?php
require_once("wsd/cw.php");
require_once("lib/host.php");

$cw = new cw();


$agents = $cw->agents();
foreach ($agents as $a) {
	$h  = host_by_ip($a->remote_ip);
	if (is_array($h) && $h != []) $h = $h[0];
	$ch = $cw->server($a->server_id);
	if (is_object($h) && $h != (object)[]) {
		print("$a->remote_ip $h->name $ch->hostname cyberwatch: " . ($h->cyberwatch ? "installed" : "not installed") . "\n"); 
	} else {
		print("$a->remote_ip $ch->hostname is not a server\n");
	} 
}
