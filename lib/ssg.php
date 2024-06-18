<?php
require_once("dbg_tools.php");
function ssg($host, $cmd) {
	#$l = gethostname();
	$out = [];
	$xit = 0;
	$bin = "ssh";
	$ccc = "$bin $host '$cmd'";
	$s = exec($ccc, $out, $xit);
	if ($xit === false) return false;
	return $out;
}
function scpg($host, $src, $dst) {
	#$l = gethostname();
	$bin = "scp";
	$xit = 0;
	$out = [];
	exec("$bin $host:$src $dst", $out, $xit); 
	if (file_exists($dst)) return true;
	return false;
}
?>
