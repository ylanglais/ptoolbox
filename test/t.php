<?php

require_once("lib/dbg_tools.php");

$timeout = 5;
$muser   = "ldapread@lfm.lan";
$mpass   = "qk92mzt";
$ldap    = false;
#$base    = "dc=lfm,dc=lan";
#$base   = "cn=User LDAP,ou=systeme,ou=utilisateurs,dc=lfm,dc=lan";


$ldap_host = "ldap://ad1.lfm.lan";
$ldap_port = "389";
$base_dn = "OU=LA FRANCE MUTUALISTE,DC=lfm,DC=lan";
$filter = "(cn=y.langlais)";
#$filter = "(samaccountname=y.langlais)";
$ldap_user =$muser;
$ldap_pass = $mpass;
$ldap = ldap_connect( $ldap_host, $ldap_port);
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

$bind = ldap_bind($ldap, $ldap_user, $ldap_pass);
#$read = ldap_search($ldap, $base_dn, $filter);
$read = ldap_search($ldap, $base_dn, $filter, [ 'memberof' ]);
$info = ldap_get_entries($ldap, $read); 

print("------\n");
print_r($info);print("\n");
print(json_encode($info)."\n");
/*
echo $info["count"]." entrees retournees\n\n"; 
for($ligne = 0; $ligne < $info["count"]; $ligne++) {
	for($colonne = 0; $colonne < $info[$ligne]["count"]; $colonne++) {
		$data = $info[$ligne][$colonne];
		echo $data.":".$info[$ligne][$data][0]."\n";
	}
	echo "\n";
}
*/
print("------\n");
$groups = ldap_list($ldap, $base_dn, $filter , ['memberOf']);
print_r($groups);print("\n");
print(json_encode($groups) ."\n");
print("------\n");
	
ldap_close($ldap);
?>
