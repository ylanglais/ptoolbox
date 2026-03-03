<?php
require_once("lib/ora.php");


#$tns="(DESCRIPTION=(LOAD_BALANCE=yes) (ADDRESS=(PROTOCOL=TCP)(HOST=oraprod1-19-vip.lfm.lan) (PORT=1521)) (ADDRESS=(PROTOCOL=TCP)(HOST=oraprod2-19-vip.lfm.lan) (PORT=1521)) (CONNECT_DATA=(SERVICE_NAME=crmprod)))";

#$db_user = "devvisu";
#$db_pass = "usivvedprd";
#$db = "$tns";
$db = false;


#$ora = new ora($db, $db_user, $db_pass);
#$ora = new ora("dbs=graal", $db_user, $db_pass);
#$ora = new ora("graal", $db_user, $db_pass);
$ora = new ora("graal");
#$ora = new ora("ora.json");

#$qry = "select count(*) as count from RFLOW_BACKEND.AOFFTSY";
$qry = "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM in ('ANGELE', 'IRIS', 'YANN')";

$q = new oqry($ora, $qry);

$i = 1;
while ($r = $q->obj()) {
	print("$i: \n"); 
	foreach ($r as $k => $v) {
		print("\t$k -> $v\n");
	}
	print("\n"); 
$i++;
}

