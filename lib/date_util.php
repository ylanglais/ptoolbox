<?php

function is_date($val) {
	if (DateTime::createFromFormat('Y-m-d', $val) !== false) return true;
	if (DateTime::createFromFormat('Y/m/d', $val) !== false) return true;
	if (DateTime::createFromFormat('d/m/Y', $val) !== false) return true;
	if (DateTime::createFromFormat('Y-m-d H:i', $val) !== false) return true;
	if (DateTime::createFromFormat('Y/m/d H:i', $val) !== false) return true;
	if (DateTime::createFromFormat('d/m/Y H:i', $val) !== false) return true;
	if (DateTime::createFromFormat('Y-m-d H:i:s', $val) !== false) return true;
	if (DateTime::createFromFormat('Y/m/d H:i:s', $val) !== false) return true;
	if (DateTime::createFromFormat('d/m/Y H:i:s.u', $val) !== false) return true;
	if (DateTime::createFromFormat('Y-m-d H:i:s.u', $val) !== false) return true;
	if (DateTime::createFromFormat('Y/m/d H:i:s.u', $val) !== false) return true;
	if (DateTime::createFromFormat('d/m/Y H:i:s.u', $val) !== false) return true;
	return false;
}
function tz_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}
function yyyymmddhhmmss() {
		$g = getdate();
		return sprintf("%4d%02d%02d%02d%02d%02d", $g['year'], $g['mon'], $g['mday'], $g['hours'], $g['minutes'], $g['seconds']);
}
function timestamp($sep='/') {
		$g = getdate();
		$u = substr(explode(".", explode(" ",microtime())[0])[1], 0, 3);
		return sprintf("%4d$sep%02d$sep%02d %02d:%02d:%02d.%03d", $g['year'], $g['mon'], $g['mday'], $g['hours'], $g['minutes'], $g['seconds'], $u);
}
function dbstamp() {
		$g = getdate();
		$u = substr(explode(".", explode(" ",microtime())[0])[1], 0, 3);
		return sprintf("%4d-%02d-%02d %02d:%02d:%02d.%03d", $g['year'], $g['mon'], $g['mday'], $g['hours'], $g['minutes'], $g['seconds'], $u);
}
function epoch() {
	return time();
}
#function epoch() {
	#return time() + tz_offset("UTC") ;
#}
function d2e($d) {
	return strtotime($d);
}
function e2d($e, $sep = '-') {
	return  date('Y'.$sep.'m'.$sep.'d H:i:s', $e);
}
function e2date($e, $sep = '-') {
	return  date('Y'.$sep.'m'.$sep.'d', $e);
}
function e2time($e) {
	return  date('H:i:s', $e);
}
function today($sep = "-") {
	return date('Y' . $sep . 'm' . $sep . 'd');
}
function this_month() {
	return date('m');
}
function this_year() {
	return date('Y');
}
function date_to_db($date) {
	if (preg_match("%([0-3][0-9])[/-]([01][0-9])[/-]([12][09][0-9][0-9])%", $date, $m))
		return "$m[3]-$m[2]-$m[1]";
	if (preg_match("%([12][09][0-9][0-9])[/-]([01][0-9])[/-]([0-3][0-9])%", $date, $m))
		return "$m[1]-$m[2]-$m[3]";
	if (preg_match("%([12][09][0-9][0-9])([01][0-9])([0-3][0-9])%", $date, $m))
		return "$m[3]-$m[2]-$m[1]";
	return false;
}
function date_to_human($date) {
	if (preg_match("%[0-3][0-9]/[01][0-9]/[12][09][0-9][0-9]%", $date)) 
		return $date;
	if (preg_match("%([0-3][0-9])-([01][0-9])-([12][09][0-9][0-9])%", $date, $m))
		return "$m[1]/$m[2]/$m[3]";
	if (preg_match("%([12][09][0-9][0-9])[/-]([01][0-9])[/-]([0-3][0-9])%", $date, $m))
		return "$m[3]/$m[2]/$m[1]";
	if (preg_match("%([12][09][0-9][0-9])([01][0-9])([0-3][0-9])%", $date, $m))
		return "$m[3]/$m[2]/$m[3]";
	return false;
}
function date_yyyymmdd_to_db($date) {
	# 0123456789
	# yyyymmdd
	$mm = substr($date, 4, 2);
	$dd = substr($date, 6, 2);
	$yyyy = substr($date, 0, 4);
	return "$yyyy-$mm-$dd";
}
function date_us_to_iso($date) {
	# 0123456789
	# mm/dd/yyyy
	$mm = substr($date, 0, 2);
	$dd = substr($date, 3, 2);
	$yyyy = substr($date, 6, 4);
	return "$yyyy/$mm/$dd";
}

function date_human_to_db($date) {
	# 0123456789
	# dd/mm/yyyy
	$dd = substr($date, 0, 2);
	$mm = substr($date, 3, 2);
	$yyyy = substr($date, 6, 4);
	return "$yyyy-$mm-$dd";
}
function date_db_to_human($date) {
	# 0123456789
	# yyyy-mm-dd
	$dd = substr($date, 8, 2);
	$mm = substr($date, 5, 2);
	$yyyy = substr($date, 0, 4);
	return "$dd/$mm/$yyyy";
}

function date_human_to_iso($date) {
	# 0123456789
	# dd/mm/yyyy
	$dd = substr($date, 0, 2);
	$mm = substr($date, 3, 2);
	$yyyy = substr($date, 6, 4);
	return "$yyyy/$mm/$dd";
}
function date_iso_to_human($date) {
	return date_db_to_human($date);
}
function date_db_to_iso($date) {
	return date_convert_sep($date, '-', '/');
}
function date_iso_to_db($date) {
	return date_convert_sep($date, '/', '-');
}
function date_convert_sep($date, $fromsep, $tosep) {
	return str_replace($fromsep, $tosep, $date);
}
function date_weekday($date = "") {
	if ($date == "") $date = $today();
	return date('D', strtotime($date));
}
function date_weekday_num($date = "") {
	if ($date == "") $date = $today();
	return date('N', strtotime($date));
}
function date_month($date = "") {
	if ($date == "") return this_month();
	return date('m', strtotime($date));
}
function date_year($date = "") {
	if ($date == "") return this_year();
	return date('Y', strtotime($date));
}
function datetime_to_date($dt) {
	return substr($dt, 0, 10);	
}
function soy($date = "", $sep = "-") {
	$y = date_year($date);
	return "$y$sep"."01$sep"."01";
}
function som($date = "", $sep = "-") {
	$m = date_month($date);
	$y = date_year($date);
	return "$y$sep$m$sep"."01";
}
function eom($date = "", $sep = "-") {
	if ($date == "") return date('Y'.$sep.'m'.$sep.'t');
	return date('Y'.$sep.'m'.$sep.'t', strtotime($date));
}
function date_next($date = "", $sep = "-") {
	return date_shift("+1 day", $date, $sep);
}
function date_prev($date = "", $sep = "-") {
	return date_shift("-1 day", $date, $sep);
}
function date_next_month($date = "", $sep = "-") {
	# Do *NOT* use date_shit +1 month since it reteurns +30 days:
	#return date_shift("+1 month", $date, $sep);
	if ($date == "") $date = today();
	
	$s = strtotime($date);
	$d = date('d', $s);
	$m = date('n', $s);
	$y = date('Y', $s);
	#$t = date('t', $s);
	$m += 1;
	if ($m > 12) { 
		$m -= 12;
		$y +=1;
	}
	if ($m < 10) {
		$m = "0$m";
	}

	$tt = date('t', strtotime("$y/$m/01"));
	
	# if ($d == $t && $d < $tt) { 
	# 	# eom correction: eom stay eom of next month:
	#	$d = $tt;
	#} else 
	if ($d > $tt) {
		# correct out of bonds date to eom:
		$d = $tt;
	}
	return "$y$sep$m$sep$d";
}
function date_prev_month($date = "", $sep = "-") {
	# Do *NOT* use date_shit -1 month since it reteurns -30 days:
	# return date_shift("-1 month", $date, $sep);
	if ($date == "") $date = today();
	
	$s = strtotime($date);
	$d = date('d', $s);
	$m = date('m', $s);
	$y = date('Y', $s);
	
	$m -= 1;
	if ($m < 1) { 
		$m += 12;
		$y -=1;
	}
	if ($m < 10) {
		$m = "0$m";
	}
	$t = date('t', strtotime("$y/$m/01"));
	if ($d > $t) $d = $t;
	return "$y$sep$m$sep$d";
}
function date_next_year($date = "", $sep = "-") {
	# Do *NOT* use date_shit +1 year since it reteurns +365 or +366 days:
	#return date_shift("+1 year", $date, $sep);
	if ($date == "") $date = today();
	
	$s = strtotime($date);
	$d = date('d', $s);
	$m = date('m', $s);
	$y = date('Y', $s);
	
	$y += 1;
	$t = date('t',  strtotime("$y/$m/01"));
	if ($d > $t) $d = $t;
	return "$y$sep$m$sep$d";
}
function date_prev_year($date = "", $sep = "-") {
	# Do *NOT* use date_shit -1 year since it returns -365 or -366 days:
	#return date_shift("-1 year", $date, $sep);
	if ($date == "") $date = today();
	
	$s = strtotime($date);
	$d = date('d', $s);
	$m = date('m', $s);
	$y = date('Y', $s);
	
	$y -= 1;
	$t = date('t',  strtotime("$y/$m/01"));
	if ($d > $t) $d = $t;
	return "$y$sep$m$sep$d";
}
function date_shift($shift, $date = "", $sep = "-") {
	if ($date == "") return date('Y'.$sep.'m'.$sep.'d', strtotime($shift));
	return date('Y'.$sep.'m'.$sep.'d', strtotime($shift, strtotime($date)));
}
function datetime_shift($shift, $date = "", $sep = "-") {
	if ($date == "") return date('Y'.$sep.'m'.$sep.'d H:i:s', strtotime($shift));
	return date('Y'.$sep.'m'.$sep.'d H:i:s', strtotime($shift, strtotime($date)));
}
function date_days_diff($date1, $date2) {
	return floor(abs(strtotime($date2) - strtotime($date1)) / 3600 / 24);
}

/* Thanks to http://www.informatix.fr/tutoriels/php/trouver-les-jours-feries-francais-en-php-137 */
function easter($year = null) {
	if ($year === null) {
		 $year = intval(strftime('%Y'));
	}
	$a = $year %  4;
	$b = $year %  7;
	$c = $year % 19;
	$m = 24;
	$n = 5;
	$d = (19 * $c + $m ) % 30;
	$e = (2 * $a + 4 * $b + 6 * $d + $n) % 7;
	$easterdate = 22 + $d + $e;
	if ($easterdate > 31) {
		$day = $d + $e - 9;
		$month = 4;
	} else {
		$day = 22 + $d + $e;
		$month = 3;
	}
	if ($d == 29 && $e == 6) {
		$day = 10;
		$month = 04;
	} elseif ($d == 28 && $e == 6) {
		$day = 18;
		$month = 04;
	}
	return sprintf("%4d-%02d-%02d", $year, $month, $day);
}

function nwd($year = null) {
	if ($year === null) {
		 $year = intval(strftime('%Y'));
	}

	$e  = easter($year);
	$ed = date('j', strtotime($e));
	$em = date('n', strtotime($e));

	$nwd = [
		"$year-01-01",
		"$year-05-01",
		"$year-05-08",
		"$year-07-14",
		"$year-08-15",
		"$year-11-01",
		"$year-11-11",
		"$year-12-25",
		date_shift("+1  day", $e), // lundi de pâques
		date_shift("+39 day", $e), // Ascencion
		date_shift("+50 day", $e), // Pentecote
	];

	sort($nwd);

	return $nwd;
}
function date_nbd_days_diff($date1, $date2) {
	$date1 = date("Y-m-d", strtotime($date1));
	$date2 = date("Y-m-d", strtotime($date2));

	$nwd = array();

	$y1 = date('Y', strtotime($date1));
	$y2 = date('Y', strtotime($date2));

	#	
	# if date diff on more than 1 year:
	for ($i = $y1; $i <= $y2; $i++) {
		$nwd = array_merge($nwd, nwd($i));
	}
	$nd = 0;
	for ($i = $date1; $i < $date2; $i = date_next($i)) {
		$dow = date('w', strtotime($i));
		if (!in_array($i, $nwd) && $dow != 6 &&  $dow != 0) {
			$nd++ ;
		}
	}
	return $nd;
}

function date_test() {
	function _pe_($str) {
		eval("\$r = $str;");
		print("$str = $r\n");
		#print("$str = " . eval("$str;") . "\n");
	}
	_pe_("today()");
	_pe_("today('/')");
	_pe_("this_month()");
	_pe_("this_year()" );
	_pe_("date_month('2023/05/21')");
	_pe_("date_year('2019/03/14')");
	_pe_("datetime_to_date('2019/03/14 12:43:57')");
	_pe_("som()");
	_pe_("som('', '/')");
	_pe_("som('2023/05/21')");
	_pe_("som('2023/05/21', '/')");
	_pe_("eom()");
	_pe_("eom('', '.')");
	_pe_("eom('2023/05/21')");
	_pe_("eom('2023/05/21', '/')");
	_pe_("date_next()");
	_pe_("date_next('', '/')");
	_pe_("date_next('2015/12/31')");
	_pe_("date_next('2020/02/28', '/')");
	_pe_("date_prev()");
	_pe_("date_prev('', '/')");
	_pe_("date_prev('2019/01/01')");
	_pe_("date_prev('2020/03/01', '/')");

	_pe_("date_next_month()");
	_pe_("date_next_month('', '/')");
	_pe_("date_next_month('2018/01/31')");
	_pe_("date_next_month('2018/01/31', '')");

	_pe_("date_prev_month()");
	_pe_("date_prev_month('', '/')");
	_pe_("date_prev_month('2018/05/31')");
	_pe_("date_prev_month('2018/05/31', '')");

	_pe_("date_next_year()");
	_pe_("date_next_year('', '|')");
	_pe_("date_next_year('2000/02/29')");
	_pe_("date_next_year('2000/02/29', '$')");

	_pe_("date_prev_year()");
	_pe_("date_prev_year('', '+')");
	_pe_("date_prev_year('2000/02/29')");
	_pe_("date_prev_year('2000/02/29', ':')");

	_pe_("date_shift(' +2 days')");
	_pe_("date_shift('-3 days', '', '/')");
	_pe_("date_shift('+2 months', '2017/07/31')");

	_pe_("date_days_diff('2018/07/21', '2018/07/12')");
	_pe_("date_days_diff('2018/07/21', '2018/07/23')");
	_pe_("date_days_diff('2018/07/21', '2018/07/21')");
}
?>
