<?php

function ping($host, $timeout = 1) {
	exec("/usr/bin/ping -c1 -W$timeout $host 2>&1 ", $o, $st);
    if (0 != $st)
		return false; 
	return true;
}

function get_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		return $_SERVER['REMOTE_ADDR'];
	}
	return false;
}

function dl_to_stc($datalink) {
	if (preg_match("/^(([^.]*)\.)?([^.]*)\.(.*)$/", $datalink, $m)) {
		return [ $m[2], $m[3], $m[4] ];	
	}
	return ["", "", "$datalink" ];
}

function gen_uuid() {
	return rtrim(file_get_contents('/proc/sys/kernel/random/uuid'));
}

function gen_elid() {
	return substr(microtime(), 2, 6);
}

function mthd_args($cls, $mthd) {
	if ($cls == null) {
		try {
			$r = new ReflectionFunction($mthd);
		} catch (\Exception $e) {
			err("invalid function name \"$mthd\"");
			return null;
		}
	} else {	
		try {
			$r = new ReflectionMethod($cls, $mthd);
		} catch (\Exception $e) {
			err("invalid class \"$cls\" or method name \"$mthd\"");
			return null;
		}
	}
	$al = [];
	foreach ($r->getParameters() as $a) {
		if (preg_match('/[ 	]*Parameter #([0-9]*) \[ <(required|optional)> (([^ ]*) )?\$([a-zA-Z0-9_]*) (= ([^ ]*) )?\]/', $a, $m)) {
			$o = (object)[];
			#foreach ($m as $i => $v) print("$i: $v\n");
			$o->name = $m[5];
			$o->num  = $m[1];
			$o->opt  = true;  if ($m[2] == "required") $o->opt  = false;
			$o->type = [] ;   if ($m[4] != "")         $o->type = explode("|", $m[4]);
			$o->hdef = false; if (count($m) >= 7)      $o->hdef = true;
			$o->def  = null;  if ($o->hdef)            $o->def  = $m[7];
			array_push($al, $o);
		} else  {
			print("no match $a\n");
		}
	}
	return $al;
} 

function pdf_2_png($in, $out, $page, $x, $y, $w, $h) {
	$im = new Imagick();
	$im->setResolution(300, 300);
	$im->readImage($in);
	$im->setIteratorIndex($page);
	$im->cropimage($w, $h, $x, $y);
	$im->writeImage($out);
}

function ncod($str) {
	return htmlentities(str_replace("''", "'", $str), ENT_NOQUOTES, "UTF-8");
}

function dcod($str) {
	return html_entity_decode(str_replace("'", "''", $str), ENT_NOQUOTES, "UTF-8");
}

function esc($s) {
    return str_replace("'", "''", $s);
}

function hvqe($s) {
	return htmlspecialchars($s);
}

function h_child($path, $node = "") {
	if ($node <> "") {
		$pos = strpos($path, $node);
		return substr($path, $pos + strlen($node) + 1, strlen($path) - 1);
	} 
	$pos = strpos($path, ".");
	return substr($path, $pos + 1, strlen($path) - 1); 
}

function h_current($path) {
	$pos = strpos($path, ".");
	return substr($path, 0, $pos);
}

function h_has_child($path) {
	if (strchr($path, ".")) {
		return true;
	}
}

function fmt_int($_int) {
	if (!is_int($_int)) return "".$_int;
	$in = sprintf("%d", $_int);
	$l  = strlen($in);
	$out = '';
	$k = 3;
	for ($i = 3; $i < $l + 3; $i += 3) {
		if ($i > $l) { 
			$k = 3 - $i + $l;
			$i = $l;
		}
		$out = substr($in, -$i, $k) . $out;
		if ($i < strlen($in)) {
			$out = "&nbsp;$out";
		}
	} 
	return $out;
}

function fmt_float($_float, $dec = -1) {
	if (!is_float($_float)) return "".$_float;
	if ($dec != -1) $f = sprintf('%.'.$dec.'f', $_float);
	else            $f = sprintf("%f", $_float);

	if (preg_match('/^([+-]?)([0-9]*)(([,.](.*)){0,1})$/', $f, $m)) {
		$sg = $m[1];
		$in = $m[2];
		$fl = $m[5];
	}
	$out = "$sg".fmt_int($in);
	if ($fl != "") $out .= ",$fl";
	return $out;
}

function unb64($data) {
	$h = "__b64__";
	$t = "__46b__";
	if (preg_match('%^'.$h.'([a-zA-Z0-9/+]*={0,2})'.$t.'$%', $data, $m))
		return base64_decode($m[1]);
	return $data;
}
function b64($data) {
	$h = "__b64__";
	$t = "__46b__";
	return $h . base64_encode($data) . $t;
}
?>
