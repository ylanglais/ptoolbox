<?php

require_once("lib/ad.php");

$ad = new ad();


$attrs = [
	'sn',
	'givenname',
	'samaccountname',
	'company',
	'displayname',
	'department',
	'departmentNumber',
	'homephone', 
	'l',
	'mail',
	'mobile',
	'personaltitle',
	'postalcode',
	'streetaddress',
	'physicalDeliveryOfficeName',
	'telephonenumber',
	'title',
	'wwwhomepage',
	'manager',
	'extensionAttribute1',
	'extensionAttribute2',
	'extensionAttribute3',
	'extensionAttribute4',
	'extensionAttribute5',
	'extensionAttribute6',
	'extensionAttribute7',
	'extensionAttribute8',
	'lastlogon',
	#msExchMailboxGuid',
	'pwdLastSet',
	'userAccountControl',
	'memberof'
];

/*
print(print_r($r, TRUE)."\n");
*/

$r = $ad->search("(&(objectclass=person)(samaccountname=*))", $attrs);
#$r = $ad->search("(&(objectclass=person)(cn=.langlais))", $attrs);
#$r = $ad->search("(cn=y.langlais)", [ ]);
foreach ($r as $i => $d) {
	print("entry $i:\n");
	foreach ($d as $k => $v) {
		if (is_array($v)) {
			print("\t$k:\n");
			foreach ($v as $j => $vv) {
				print("\t\t$j: $vv\n");
			}
		} else {
			print("\t$k: $v\n");
		}
	}
/***
	foreach ($attrs as $a) {
		if (property_exists($d, $a)) print("\t$a: ".$d->$a."\n");
		else print("\t$a:\n");
	}
***/
}



