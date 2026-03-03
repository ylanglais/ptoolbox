<?php
require_once("lib/ora.php");
require_once("lib/dbg_tools.php");

$db = new ora("graalrec");

#$q = new oqry($db, "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM in ('YANN')");
#$q = new oqry($db, "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM in ('ANGELE', 'IRIS', 'YANN')");
$q = new oqry($db, "select CL_RUB1, NOM, PRENOM, ADRESSE, CODE_POSTAL, VILLE, CL_INFO_COMP5, CL_EMAIL,  to_char(CL_INFO_COMP6, 'DD/MM/YYYY') as CL_INFO_COMP6 from CRM.CLIENTS where CL_RUB1 in (
	'367659',
	'646915',
	'666890',
	'667831',
	'678699',
	'679170',
	'688395',
	'700780',
	'711935',
	'714532',
	'720243',
	'729992',
	'731018',
	'797396',
	'800666',
	'803907',
	'822048',
	'837158',
	'840881',
	'848338',
	'850569',
	'866641',
	'884842',
	'902390',
	'943283',
	'952611',
	'962976',
	'968672',
	'982279',
	'1013414',
	'1020596',
	'1035250',
	'1036998',
	'1039810'
)");

$cols= [ "CL_RUB1" => "ID", "NOM" => "NOM", "PRENOM"=>"PRENOM", "ADRESSE" => "ADRESSE", "CODE_POSTAL" => "CP", "VILLE" => "VILLE", "CL_INFO_COMP5" => "TEL", "CL_EMAIL" => "EMAIL",  "CL_INFO_COMP6" => "DATENAISSANCE" ];
$i=0;
print("n°"); foreach ($cols as $c => $n) print(";$n"); print("\n");
while ($o = $q->obj()) {
	$i++;
	print("$i");
	foreach ($cols as $k => $n) {
		print(";". $o->$k);
	}
	print("\n");
}
