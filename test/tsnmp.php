<?php

$host = "barracuda";
$com  = "public";
$mib  = "BESG.mib";
if (snmp_read_mib($mib) === false) err("bad mib file");

#echo snmpget($host, $com, "Barracuda-SPAM::systemLoad") . "\n";
#echo snmpget($host, $com, "Barracuda-SPAM::dailyOutboundSpamBlocked") . "\n";

$base = "";
if (count($argv) > 1) $base = $argv[1];

print("base = '$base'\n");

print_r(snmprealwalk($host, $com, $base));
exit;
$a = snmpwalk($host, $com, $base);
foreach ($a as $val) {
    echo "$val\n";
}
exit;
$a = snmpwalkoid($host, $com, $base);
for (reset($a); $i = key($a); next($a)) {
    print("$i: ".$a[$i]."\n");
}
?>
