<?php


$tns="(DESCRIPTION=(LOAD_BALANCE=yes) (ADDRESS=(PROTOCOL=TCP)(HOST=oraprod1-19-vip.lfm.lan) (PORT=1521)) (ADDRESS=(PROTOCOL=TCP)(HOST=oraprod2-19-vip.lfm.lan) (PORT=1521)) (CONNECT_DATA=(SERVICE_NAME=crmprod)))";

$db_username = "devvisu";
$db_password = "usivvedprd";
$db = "oci:dbname=$tns";

#foreach(PDO::getAvailableDrivers() as $driver)
#    echo $driver, '\n';
#exit;

#$cn = new PDO($db, $db_username, $db_password);
if (($cn = oci_connect($db_username, $db_password, $tns, 'UTF8')) === false) {
	print("Cannot connect to db using $tns: " . oci_error() . "\n");
	exit(1);
}
#$qry = "select count(*) as count from RFLOW_BACKEND.AOFFTSY";
#$qry = "select * from CRM.CLIENTS where NOM = 'LANGLAIS' and PRENOM in ('ANGELE', 'IRIS', 'YANN')";
$qry = "select * from CRM.CLIENTS where CL_INFO_COMP35 like '%é%'";

if (($stmt = oci_parse($cn, $qry)) === false) {
	print("Cannot proces query $qry: " . oci_error() . "\n");
	exit(2);
}

if (($status = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) === false) {
	print("Error executing query $qry: " . oci_error() . "\n");
	exit(3);
}

$i = 1;
while ($r = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
	print("$i:\n");
	foreach ($r as $k => $v) {
		print("\t$k: $v\n");
	}
	print("\n");
}

