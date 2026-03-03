<?php
require_once("lib/dbg_tools.php");
require_once("lib/curl.php");

class vmw {

	function __construct(){ 
		$this->host  = false;
		$this->token = false;
		$this->c     = false;

		$f = "conf/vmw.php";
		if (!file_exists($f)) {
			err("no vmw config file");
			return;
		}
		include($f);
		$this->host = $vmw_host ;	
		
		$ba = "Authorization: Basic " .  base64_encode("$vmw_user:$vmw_pass");

		$this->c = new curl ($this->host, [ $ba, "Content-type: application/json" ], false, false);	
		$r = $this->c->post("session", null);
		if ($r === false) {
			$this->c = null;
			err("Bad authentification");
		}
		#info("token = '$r'");
		$r = str_replace('"', '', $r);
		$this->c->header( [ "Content-type: application/json", "vmware-api-session-id: $r" ]);
	}
	function hosts() {
		if ($this->c === false) return;
		if (($r = $this->c->get("vcenter/host")) === false) return false;
		return json_decode($r);
	}
	function host($id) {
		if($this->c === false) return;
		if (($r = $this->c->get("vcenter/vm/$id")) === false) return false;
		return json_decode($r);
	}
	function vms() {
		return $this->vm();
	}
	function vm($id = "") {
		if($this->c === false) return;
		if ($id != "") $id = "/$id";
		if (($r = $this->c->get("vcenter/vm$id")) === false) return false;
		return json_decode($r);
	}
} 

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
$on  = $off = $count = 0;
$cores = $ram = 0;
foreach ($vms as $vm) {
	#info(json_encode($vm));
	if (($d = $vmw->vm($vm->vm)) !== false) {
		print("$vm->vm:\n");
		print("\tname   : " .  $d->name); 
		print("\n\tpower: " . ($vm->power_state == "POWERED_ON" ? "on": "off")); 
		print("\n\tcores: " . ($d->cpu->cores_per_socket * $d->cpu->count));
		print("\n\tmem  : " . ($d->memory->size_MiB / 1000));
		print("\n\tOS   : " .  $d->guest_OS); 
		print("\n\tother: " . json_encode($d));
		$f = os_family($d->guest_OS);
		$s = os_distro($d->guest_OS);
		$cores += ($d->cpu->cores_per_socket * $d->cpu->count);
		$ram   += $d->memory->size_MiB / 1000;
	
		if (!array_key_exists($d->guest_OS, $OS)) $OS[$d->guest_OS] = 0;
		if (!array_key_exists($f, $fam)) $fam[$f] = 0;
		if (!array_key_exists($s, $dst)) $dst[$s] = 0;
		$OS[$d->guest_OS]++;
		$fam[$f]++;
		$dst[$s]++;
		if ($vm->power_state == "POWERED_ON") {
				$on++;
		} else {
			$off++;
		}
		$count++;
		print("\n");
	} else {
		info("$vm->name not found");
	}
}
print("count: $count, on: $on, $off: $off\n");
print("cores: $cores, avg cores: " . round($cores/$count, 1) . "\n");
print("RAM  : ". round($ram) . ",   avg RAM  : " . round($ram/$count)      . "\n");
arsort($OS, SORT_NUMERIC);
print("\nOS:\n");
foreach ($OS as $o => $n) {
	printf("%20s: %5d\n", $o, $n);
}
arsort($fam, SORT_NUMERIC);
print("\nOS families:\n");
foreach ($fam as $o => $n) {
	printf("%20s: %5d\n", $o, $n);
}
arsort($dst, SORT_NUMERIC);
print("\nOS distros:\n");
foreach ($dst as $o => $n) {
	printf("%20s: %5d\n", $o, $n);
}

?>
