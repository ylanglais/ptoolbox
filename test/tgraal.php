<?php
require_once("lib/ora.php");
require_once("lib/dbg_tools.php");

$db = new ora("graalrec");

$q = new oqry($db, "select * from CRM.CLIENTS where NOM = :TOTO and PRENOM = :TITI", [ ":TOTO" => "LANGLAIS", ":TITI" => "IRIS" ]);
#$q = new oqry($db, "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM = 'IRIS'");
#$q = new oqry($db, "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM in ('ANGELE', 'IRIS', 'YANN')");

while ($o = $q->obj()) {
	foreach ($o as $k => $v) {
		if ($v != "") print("$k --> $v\n");
	}
	print("\n");
}
