<?php
require_once("lib/ora.php");
require_once("lib/dbg_tools.php");
require_once("lib/csv.php");
require_once("lib/util.php");

$db = new ora("graalrec");


$c = new csv("Deces_2024_M10.csv");

for ($i = 0; $i < $c->nlines(); $i++) {
#for ($i = 0; $i < 100; $i++) {
	$nomprenom = $c->get($i, "nomprenom");
	if (!preg_match('|^([^*]*)\*(.*)/$|', $nomprenom, $m)) {
		dbg("nomatch $nomprenom");
		continue;
	}	
	$en = esc($nom = $m[1]);
	$ep = esc($prenom = $m[2]);
	$dob = $c->get($i, "datenaiss");
	$g   = $c->get($i, "sexe");
	$cp  = $c->get($i, "lieudeces");
	$ddc = $c->get($i, "datedeces");

	if      ($dob             == "00000000") $ddd = "";
	else if (substr($dob, -4) == "0000")     $ddd = " and to_char(CL_INFO_COMP6, 'YYYY')   = substr('$dob', 0, 4)";
	else if (substr($dob, -2) == "00")       $ddd = " and to_char(CL_INFO_COMP6, 'YYYYMM') = substr('$dob', 0, 6)";
	else                                     $ddd = " and CL_INFO_COMP6 = to_date('$dob', 'YYYYMMDD')";

#CODE_POSTAL, 

	$n = 3;
	$q = new oqry($db, "select NOM, PRENOM, AP_CODE, to_char(CL_INFO_COMP6, 'YYYYMMDD') as DOB,  CODE_POSTALfrom CRM.CLIENTS where NOM = '$en' and PRENOM = '$ep' and AP_CODE = $g $ddd");
	if (($o = $q->obj()) == false) {
		$n = 2;
		$q = new oqry($db, "select NOM, PRENOM, AP_CODE, to_char(CL_INFO_COMP6, 'YYYYMMDD') as DOB, CODE_POSTAL from CRM.CLIENTS where NOM = '$en' and AP_CODE = $g $ddd");
		if (($o = $q->obj()) == false) {
			$n = 1;
			$q = new oqry($db, "select NOM, PRENOM, AP_CODE, to_char(CL_INFO_COMP6, 'YYYYMMDD') as DOB, CODE_POSTAL from CRM.CLIENTS where NOM = '$en' and PRENOM = '$ep' and AP_CODE = $g");
		}
	}
	if ($o != false ) {
		print("$i: n: $n, GRAAL: AP_CODE: $o->AP_CODE, DOB: $o->DOB, NOM: $o->NOM, PRENOM: $o->PRENOM, CP: $o->CODE_POSTAL\n");
		print("$i: n: $n, INSEE: AP_CODE: $g, DOB: $dob, NOM: $nom, PRENOM: $prenom, CPDC: cp, $ddc\n");
 
		#print("$k --> $v\n");
	}
}
