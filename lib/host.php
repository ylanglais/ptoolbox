<?php
require_once("lib/query.php");
require_once("lib/prov.php");
require_once("lib/ssg.php");
require_once("lib/dbg_tools.php");

function host_groups() {
	$q = new query("select * from infra.hgrp");
	return $q->all();
}
function host_list($opt = false) {
	$q = new query("select * from infra.host");	
	return $q->all();
}
function host_get($name) {
	$p = new prov("db", "default.infra.host");
	return $p->get([ "name" => $name ]);
}
function host_by_ip($ip) {
	$p = new prov("db", "default.infra.host");
	return $p->get([ "ip" => $ip ]);
}
	
function host_put($data) {
	$p = new prov("db", "default.infra.host");
	return $p->put($data);
}

function host_linux_version($host) {
	$out = ssg($host, "cat /etc/os-release");
	$a = (object) [];
	foreach ($out as $l) {
		if (preg_match("/^([^=]*)=\"?([^\"]*)\"?$/", $l, $m)) {
			if      ($m[1] == 'ID')         $a->id      = $m[2];
			else if ($m[1] == 'VERSION_ID') $a->version = $m[2];
			else if ($m[1] == 'ID_LIKE')    $a->like    = $m[2];
		}
	}
	return $a;
}

?>
