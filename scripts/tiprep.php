<?php
require_once("lib/dbg_tools.php");
require_once("lib/iprep.php");
require_once("lib/rdap.php");

$iprep = new iprep();
$rdap  = new rdap();

if ($argc == 2 && is_file($argv[1])) {
	$f = fopen($argv[1], "r");
	while(! feof($f))  {
		$i = chop(fgets($f));
		if ($i == "") continue;
		$r = $rdap->get($i); 
		$t = $iprep->get($i); 
		if ($r !== false) {
			if (property_exists($r, "country")) $c = $r->country;
			else                                $c = "";
			print("$i: name: " . $r->name . ", country: ". $c . ", threat: ". $t->threat . ", risk level: " . $t->risk_level . "\n");
		} else {
			print("$i: threat: ". $t->threat . ", risk level: " . $t->risk_level . "\n");
		}
	}
	fclose($f);
} else {
	for ($j = 1; $j < $argc; $j++) {
		$t = $iprep->get($i = $argv[$j]);
		$r = $rdap->get($i); 
print(json_encode($t)."\n");
		if ($r !== false) {
			if (property_exists($r, "country")) $c = $r->country;
			else                                $c = "";
			print("$i: name: " . $r->name . ", country: ". $c . ", threat: ". $t->threat . ", risk level: " . $t->risk . "\n");
		} else {
			print("$i: threat: ". $t->threat . ", risk level: " . $t->risk_level . "\n");
		}

	}
}
?>
