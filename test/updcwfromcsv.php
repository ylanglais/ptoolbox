<?php

require_once("lib/dbg_tools.php");
require_once("lib/host.php");

if (count($argv) < 2) {
	err("no file given");
	exit(1);
}

foreach (file($argv[1]) as $l) {
	$l = trim($l);
	#dbg("process $l");
	if (!is_array($s = host_get($l)) || $s == []) {
		warn("$l not known");
		warn($s);
	} else {
		$h = $s[0];
		if ($h->cyberwatch == false) {
			$h->cyberwatch = true;
			info("update $l cyberwatch status");
			host_put($h);
		} else {
			info("$l is ok");
		}
	}
}
