<?php
require_once("lib/dbg_tools.php");
require_once("wsd/vmw.php");
require_once("lib/host.php");
require_once("lib/util.php");
require_once("lib/date_util.php");

function os_family($os) {
	$fs = [
		"WIN"    => "WINDOWS",
		"BSD"    => "xBSD",
		"LINUX"  => "LINUX",
		"UBUNTU" => "LINUX",
		"DEBIAN" => "LINUX",
		"RHEL"   => "LINUX",
		"CENTOS" => "LINUX",
		"SLES"   => "LINUX",
	];	
	foreach ($fs as $o => $f) if (strstr($os, $o)) return $f;
	return "OTHER";
}

function os_distro($os) {
	$fs = [
		"WIN"    => "WINDOWS",
		"BSD"    => "xBSD",
		"ORACLE" => "ORACLE",
		"UBUNTU" => "UBUNTU",
		"DEBIAN" => "DEBIAN",
		"RHEL"   => "REDHAT",
		"CENTOS" => "CENTOS",
		"SLES"   => "SUSE",
		"LINUX"  => "LINUX",
	];	
	foreach ($fs as $o => $f) if (strstr($os, $o)) return $f;
	return "OTHER";
}

$vmw = new vmw();
$vms = $vmw->vms();
$OS  = $fam = $dst = [];

#
foreach ($vms as $vm) {
	$a = (object) [];

	dbg($vm->name);

	$a->name = $vm->name;
	$a->ip   = gethostbyname($vm->name);
	$a->isvm = true;

	if ($vm->power_state == "POWERED_ON") 
		$a->ison = true;
	else 
		$a->ison = false;

	if (($d = $vmw->vm($vm->vm)) !== false) {
		if (property_exists($d, "guest_OS")) {
			$a->osstring = $d->guest_OS;
			$a->osfamily = os_family($d->guest_OS);
			$a->distrib  = os_distro($d->guest_OS);
		}

		if (strstr($a->name, "test"))         $a->env = "test";
		else if (strstr($a->name, "rec"))     $a->env = "rec";
		else if (strstr($a->name, "dev"))     $a->env = "dev";
		else if (strstr($a->name, "pprd"))    $a->env = "pprd";
		else if (strstr($a->name, "pprod"))   $a->env = "pprd";
		else if (strstr($a->name, "preprod")) $a->env = "pprd";
		else if (strstr($a->name, "prd"))     $a->env = "prod";
		else if (strstr($a->name, "prod")) 	  $a->env = "prod";
		else $a->env = "other";
	}
	if (ping($a->name)) $a->lastping = dbstamp();

	host_put($a);
}
?>
