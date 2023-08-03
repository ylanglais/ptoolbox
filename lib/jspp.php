<?php
require_once("lib/dbg_tools.php");
require_once("lib/locl.php");

function jspp($d) {
	if      (is_object($d))  return jspp_object($d);
	else if (is_array($d))   return jspp_array($d);
	else if (is_bool($d))    return jspp_bool($d);
	else if (is_null($d))    return jspp_null($d);
	else if (is_numeric($d)) return jspp_numeric($d);
	else if (is_string($d))  return jspp_string($d);
	else err("cannot handle " .gettype($d) . " (" . json_encode($d) . ")");
	return "";
}
function jspp_null($d) {
	if ($d === null) return "null";
	return "#error - null";
}
function jspp_bool($d) {
	if ($d === false) return "false";
	if ($d === true)  return "true";
	return "#error - bool";
}
function jspp_numeric($d) {
	$lc = new locl();
	if (!is_numeric($d)) return "#error - numeric";
	return $lc->format($d);
}
function jspp_string($d) {
	if (!is_string($d)) return "#error - string";
	return $d;
}
function jspp_object($d) {
	if (!is_object($d)) return "#error - object " . gettype($d);
	$s = "<table class='flist'>";
	foreach ($d as $k => $v) {
		$s .= "<tr><th class='w120'>$k</th><td>".jspp($v)."</td></tr>";
	} 
	return $s . "</table>\n";
}
function jspp_array($d) {
	if (!is_array($d)) return "#error - array";
	return jspp_object((object) $d);
}


?>
