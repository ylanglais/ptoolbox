<?php

require_once("lib/ora.php");
require_once("lib/dbg_tools.php");

$db = new ora("quidrec");
$list = json_decode('[ {"base": "DEM_DEMANDE", "fields": [ "CODEVT", "CANAL", "UTICRE" ]}, {"base": "DEM_DEMANDE_DETAIL", "fields": [ "MEDIA_TYPE", "SENS", "STATUT" ]}]');

#dbg($list);

/*
$base = "DEM_DEMANDE";
$fld  = "CODEVT";
$fld  = "CANAL";
$fld  = "UTICE";
$base = "DEM_DEMANDE_DETAIL";
$fld  = "MEDIA_TYPE";
$fld  = "SENS";
$fld  = "TYPFICHIER"; # unique instances +/-
$fld  = "STATUT";
$base = "DEM_DEMANDE_LOG";
$fld  = "APPLICATIONNAME";
$fld  = "ENDUSERNAME";
#$fld  = "SERVICENAME";

$q = new oqry($db, "select $fld, count(*) as COUNT from $base group by $fld order by count desc");
printf("%-30s:	%-10s\n", $fld, "COUNT");
while ($o = $q->obj()) {
	printf("%-30s:	%10d\n", $o->$fld, $o->COUNT);
}
print("\n")
*/

$tables  = [ "DEM_DEMANDE", "DEM_DEMANDE_DETAIL", "DEM_DEMANDE_LOG" ];
#$iddemcli = '10654551';
$iddemcli = '8702365';
$iddemcli = '31f991b6-4301-437a-98d5';
$nnn = count($argv);
$iddeml = [];
if ($nnn > 1) {
	for ($iii = 1; $iii < $nnn; $iii++) array_push($iddeml, $argv[$iii]);
} else {
	$iddeml = [ $iddemcli ];
}

foreach ($iddeml as $iddemcli) {
	$q = new oqry($db, "select IDDEMANDE from DEM_DEMANDE where IDDEMANDE_CLIENT = '$iddemcli'");
	print("\nDEMANDE CLIENT $iddemcli\n");
	print(">>> select IDDEMANDE from DEM_DEMANDE where IDDEMANDE_CLIENT = '$iddemcli'\n");

	$iddem = [];
	while ($o = $q->obj()) {
		print("\tIDDEMANDE: $o->IDDEMANDE\n");
		array_push($iddem, $o->IDDEMANDE);
	}
	foreach ($iddem as $idd) {
		print("\t$idd:\n");
		foreach ($tables as $t) {
			$q = new oqry($db, "select * from $t where IDDEMANDE = '$idd'");
			print("\t $t:\n");
			$i = 0;
			while ($o = $q->obj()) {
				$i++;
				print("  $i: \n");
				foreach ($o as $k => $v) {
					if (is_object($v)) {
						$b = $v->load();	
						printf("\t%-20s: %s\n", $k, $b);
					
					} else printf("\t%-20s: %s\n", $k, $v);
				} 
			}
			print("\n");
		}
	}
}

?>
