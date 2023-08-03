<?php
function ssg($host, $cmd) {
	#$l = gethostname();
	$out = [];
	$xit = 0;
	$bin = "ssh";
	exec("$bin $host '$cmd'", $out, $xit);
	if ($xit != 0 && count($out) == 0) return false;
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
