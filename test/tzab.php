<?php

require_once("lib/dbg_tools.php");
require_once("wsd/zabbix.php");
require_once("lib/host.php");

$z = new zabbix();

$zhosts = $z->host_get();
$n = count($zhosts);
$i = 0;
foreach ($zhosts as $zh) {
	$i++;
	$hn = strtolower($zh->host);
	if (substr($hn, -8) == ".lfm.lan") $hn =  substr($hn, 0, -8);
	dbg("$i/$n: $zh->host");
	$h = host_get($hn);
	if (is_array($h) && $h != []) $h = $h[0];
	if (!$h || !is_object($h)) {
		warn("$hn not found");
	} else {
		$c = false;
		if ($h->monitored == false && $zh->host == 0) {
			$h->monitored = true;
			dbg("$h->name is monitored");
			$c = true;
		}
		if ($zh->description != "") {
			if ($h->comments != "" && $h->comments != 'Obsolete') {
				dbg("$h->name has a comment ($h->comments) but description exists ($zh->description)");
			} else {
				$h->comments = $zh->description;
				$c = true;
				dbg("$h->name add zabbix comment \"$h->comments\"");
			}
		}
		if ($c) host_put($h);
	}
}

