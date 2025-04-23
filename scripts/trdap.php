<?php
require_once("lib/dbg_tools.php");
require_once("lib/rdap.php");

$rdap = new rdap();

if ($argc == 2 && is_file($argv[1])) {
	$f = fopen($argv[1], "r");
	while(! feof($f))  {
		$i = chop(fgets($f));
		if ($i == "") continue;
		$r = $rdap->get($i);
		if ($r !== false) {
			if (!property_exists($r, "name")) print("$i: ". json_encode($r)."\n"); 
			else if (  !property_exists($r, "country"))
				print("$i: name: " . $r->name . "\n");
			else
				print("$i: name: " . $r->name . ", country: ". $r->country . "\n");
		} else {
			print("$i: bad ip/no data\n");
		}
	}
	fclose($f);
} else {
	for ($j = 1; $j < $argc; $j++) {
		$r = $rdap->get($i = $argv[$j]);
	#	$t = iprep($i); 
		if ($r !== false) {
			if (!property_exists($r, "name")) print("$i: ". json_encode($r)."\n"); 
			else if (  !property_exists($r, "country"))
				print("$i: name: " . $r->name . "\n");
			else
				print("$i: name: " . $r->name . ", country: ". $r->country . "\n");

		} else {
			print("$i: bad ip/no data\n");
		}
	}
}
?>
