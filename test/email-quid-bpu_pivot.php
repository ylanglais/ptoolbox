<?php
require_once("lib/ora.php");
require_once("lib/dbg_tools.php");

function hasmx($dom) {
	global $domains;
	global $baddoms;

	if (array_key_exists($dom, $domains)) 
		return true;
	if (array_key_exists($dom, $baddoms)) 
		return false;
	
	$h = [];
	if (getmxrr($dom, $h)) 
		return true;
	return false;
}
function dmp_k($o) {
	$a = [];
	foreach ($o as $k => $v) { array_push($a, $k); }
	print(implode(";", $a) . "\n");
}
function dmp($o) {
	$a = [];
	foreach ($o as $k => $v) { array_push($a, $v); }
	print(implode(";", $a) . "\n");
}

$RR='^[a-zA-Z0-9_.-]+@([a-zA-Z0-9.-]+\.[a-zA-Z0-9]+$)';
#$RR= '^[^\s@]+@[^\s@]+\.[^\s@]+$';

$SRC = 10;

$db = new ora("quidprod");

$q = new oqry($db, "select count(*) as COUNT from LFM_BPU.BPU_PIVOT p, LFM_BPU.BPU_ETENDUE e where  p.IDBPU = e.IDBPU and e.IDAPPLICATIONSOURCE = $SRC");
$nn = $q->obj()->COUNT;
printf("Lignes:              %7d\n", $nn);

$q = new oqry($db, "select count(*) as COUNT from LFM_BPU.BPU_PIVOT p, LFM_BPU.BPU_ETENDUE e where  p.IDBPU = e.IDBPU and e.ADRESSEMAIL is null and e.IDAPPLICATIONSOURCE = $SRC");
$vides = $q->obj()->COUNT;
printf("Lignes sans email:   %7d\n", $vides);

$q = new oqry($db, "select count(*) as COUNT from  LFM_BPU.BPU_PIVOT p, LFM_BPU.BPU_ETENDUE e where p.IDBPU = e.IDBPU and e.ADRESSEMAIL is not null and e.IDAPPLICATIONSOURCE = $SRC");
$tocheck = $q->obj()->COUNT;
printf("Lignes à vérifer:    %7d\n", $tocheck);

$q = new oqry($db, "select p.IDBPU, p.IDADHERENT, p.NOMUSAGE, p.PRENOM1, e.ADRESSEMAIL as ADRESSEMAIL from LFM_BPU.BPU_PIVOT p, LFM_BPU.BPU_ETENDUE e where p.IDBPU = e.IDBPU and e.ADRESSEMAIL is not null and e.IDAPPLICATIONSOURCE = $SRC");


$domains = [];
$baddoms = [];

$n = $mx = $nomx = $noemail = $extra = $coma = $maybe = $oops = $bad = 0;
while ($o = $q->obj()) {
	$n++;
	if ($n % 1000 == 0) dbg("$n/$tocheck");
	if ($o->ADRESSEMAIL == '') {
		$noemail++;	
		continue;
	}
	if (!filter_var($o->ADRESSEMAIL, FILTER_VALIDATE_EMAIL)) {
		if (filter_var(preg_replace("/ /", "", $o->ADRESSEMAIL), FILTER_VALIDATE_EMAIL)) {
			$extra++;
		} else if (filter_var(preg_replace("/,/", ".", $o->ADRESSEMAIL), FILTER_VALIDATE_EMAIL)) {
			$coma++;
		} else if (preg_match("/[A-Za-z0-9._-]+@[A-Za-z0-9._-]+.[A-Za-z]*/", $o->ADRESSEMAIL)) {
			$maybe++;
		} else {
			$bad++;
		}
	} else {
		$a = [];
		if (!preg_match("/^.*@(.*)$/", $o->ADRESSEMAIL, $m)) {
			$oops++;
			continue;
		}
		$dom = strtolower($m[1]);
		if (hasmx($dom)) {
			$mx++;
			if (!array_key_exists($dom, $domains)) $domains[$dom] = 1;
			else $domains[$dom]++;
		} else {
			if (!array_key_exists($dom, $baddoms)) $baddoms[$dom] = 1;
			else $baddoms[$dom]++;
			$nomx++;
		}
	}
}
printf("Lignes testées:      %7d\n", $n);
printf("Emtpy:               %7d\n", $noemail);
printf("Possibly correct:    %7d\n", $mx);
printf("Incorrect domain:    %7d\n", $nomx);
printf("Extra space:         %7d\n", $extra);
printf("Coma instead of dot: %7d\n", $coma);
printf("Maybe actual email:  %7d\n", $maybe);
printf("No email detected:   %7d\n", $bad);
printf("Oops:                %7d\n", $oops);

arsort($domains);
print("\nDomaines ok:\n");
foreach ($domains as $dom => $c) 
	printf("%-40s: %7d\n", $dom, $c);

arsort($baddoms);
print("\nBad Domains:\n");
foreach ($baddoms as $dom => $c) 
	printf("%-40s: %7d\n", $dom, $c);


