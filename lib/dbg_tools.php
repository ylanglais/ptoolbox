<?php
require_once("lib/date_util.php");

#
# Store wd globally:
$_dbg_tools_WD = getcwd();

#
# Print stuff in html:
function html_err($str)  { print("<p><font color=\"red\">   <b><i>Error:  </i></b> $str</font></p>\n"); }
function html_warn($str) { print("<p><font color=\"orange\"><b><i>Warning:</i></b> $str</font></p>\n"); }
function html_info($str) { print("<p><font color=\"green\"> <b><i>Info:   </i></b> $str</font></p>\n"); } 
function html_dbg($str)  { print("<p><font color=\"blue\">  <b><i>Debug:  </i></b> $str</font></p>\n"); } 
function html_array($name, $arr) {
	print("<table>\n");
	print("\t<tr bgcolor='lightblue'><td colspan='2'>$name</td></tr>\n");
	print("\t<tr bgcolor='lightblue'><td><b><i>Key</i></b></td><td><b><i>Value</i></b></td></tr>\n");
	foreach($arr as $key => $value) {
		if (is_object($value) || is_array($value)) 
			print("\t<tr><td><b>". $key."</b></td><td>". serialize($value) ."</td></tr>\n");
		else 
			print("\t<tr><td><b>". $key."</b></td><td>". $value ."</td></tr>\n");
	}
	print("</table>\n");
}
#
#
function clean_path($file) {
	global $_dbg_tools_WD;
	$file = str_replace("$_dbg_tools_WD/", "", $file);
	if (preg_match_all("|/|", $file) > 1) { 
		$file = basename(dirname($file)) . "/" . basename($file); 
	}
	return $file;
}
function caller($level = 0) {
	global $_dbg_tools_WD;
	$b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $level + 1);
	$file = clean_path($b[$level]['file']);
	return "$file:" . $b[$level]['line'] . " (" . $b[$level]['function'] . ")";
}
#
# Print steps/trace:
function _step() {
	static $t = 0;
	global $_dbg_tools_WD;
	$file = caller(1);
	if (php_sapi_name() === 'cli') 
		error_log(timestamp() . " STEP: $file $t");
	else 
		error_log("STEP: $file $t");
	$t++;
}
#
# Write to error_log:
function _write($lvl, $s) {
	if (is_object($s) || is_array($s)) {
		$s = json_encode($s);
	}
	$file = caller(2);
	if (php_sapi_name() === 'cli') {
		error_log(timestamp() . " $lvl: $file $s");
	} else {
		error_log(" $lvl: $file $s");
	}
}
#
# Further helpers:
function _err($s)  { _write("ERR", $s); }
function _warn($s) { _write("WARN", $s); }
function _info($s) { _write("INFO", $s); }
function _dbg($s)  { _write("DBG",  $s); }

function err($s)  { _write("ERR", $s); }
function warn($s) { _write("WARN", $s); }
function info($s) { _write("INFO", $s); }
function dbg($s)  { _write("DBG",  $s); }
?>
