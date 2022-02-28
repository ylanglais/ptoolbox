<?php

require_once("lib/args.php");
require_once("lib/glist.php");
require_once("lib/prov.php");
require_once("lib/dbg_tools.php");

$a = new args();

$prov = $a->val("prov");
$opts = $a->val("opts");

#dbg(json_encode($prov));
#dbg(json_encode($opts));

print(glist(new prov($prov), $opts));