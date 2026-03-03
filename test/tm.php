<?php
require_once("lib/dbg_tools.php");

$lns = [
	'Aug  8 06:25:14 mcprod001 espace-perso-frontend[86412]: Access | 2023/08/08 06:25:14.821 176.173.15.215 "GET /fonts/foundation-icons.woff HTTP/1.1" 200 32020 "https://espaceperso.la-france-mutualiste.fr/" "Mozilla/5.0 (iPhone; CPU iPhone OS 16_1_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1"',
	'Aug  8 06:25:10 mcprod001 espace-perso-frontend[86412]: Access | 2023/08/08 06:25:10.459 127.0.0.1 "GET / HTTP/1.1" 301 169 "-" "curl/8.1.1"',
	'Aug  8 06:25:40 mcprod001 espace-perso-frontend[86412]: Access | 2023/08/08 06:25:40.714 62.34.148.14 "GET /secure/api/parametrage/libellePiece HTTP/1.1" 200 2040 "https://espaceperso.la-france-mutualiste.fr/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5.2 Safari/605.1.15"'
];

function po($stamp,  $ip, $cmd, $api, $status) {
	print("OK: $stamp, $ip, $cmd, $api, $status\n");
}

function pb($stamp, $ip, $url, $status) {
	print("KO: $stamp, $ip, $dd, $api, $status\n");
}


foreach ($lns as $l) {
 	#if (preg_match('#^[^\|]*\| ([^ ]* [^ ]*) ([^ ]*) "([^ ]*) ([^ ]*) HTTP/1.1" "([^"]*)".*$#', $l, $m)) {
 	if (preg_match('#^[^\|]*\| ([^ ]* [^ ]*) ([^ ]*) "([^ ]*) ([^ ]*) HTTP/1.1" ([^ ]*) .*$#', $l, $m)) {
		print("OK\n");
	} else { print("NOT OK\n");	}
}


exit;


foreach ($lns as $l) {
	if (preg_match('#^([^ ]* [^ ]*) ([^ ]*) ([^ ]*) "([^ ]*) ([^"]*) HTTP/1.1" ([0-9]*) .*#', $l, $m)) {
		#
		# access log:
		[ $all, $stamp, $host, $ip, $cmd, $api, $status ] = $m;
		dbg("access log");
		po($stamp, $ip, $cmd, $api, $status);
	} else if (preg_match('#^([^ ]* [^ ]*) ([^ ]*) ([^ ]*) "([^"]*)" ([0-9]*) .*"#', $l, $m)) {
		#
		# "bad" access log:
		[ $all, $stamp, $host,  $ip, $url, $status ] = $m;
		dbg("bad access log");
		pb($stamp, $ip, $url, $status);
	} else if (preg_match('#^[^\|]*\| ([^ ]*) \[([^\]]*)\] "([^ ]*) ([^ ]*) HTTP/1.1" ([^ ]*) .*$#', $l, $m)) {
		#
		# Original access log format out of lb:
		[ $all, $ip, $dd, $cmd, $api, $status ] = $m;
		$d = new DateTime($dd);
		$loc = (new DateTime)->getTimezone();
		$d->setTimezone($loc);
		$stamp = $d->format("Y/m/d H:i:s.v");
		dbg("Original lb log");
		po($stamp, $ip, $cmd, $api, $status);
	} else if (preg_match('#^[^\|]*\| ([^ ]*) \[([^\]]*)\] "([^"]*)" ([^ ]*) .*$#', $l, $m)) {
		#
		# Original BAD access log format out of lb:
		[ $all, $ip, $dd, $url, $status ] = $m;
		$d = new DateTime($dd);
		$loc = (new DateTime)->getTimezone();
		$d->setTimezone($loc);
		$stamp = $d->format("Y/m/d H:i:s.v");
		dbg("Bad original lb log");
		pb($stamp, $ip, $url, $status);

	} else if (preg_match('#^[^\|]*\| ([^ ]* [^ ]*) ([^ ]*) "([^ ]*) ([^ ]*) HTTP/1.1" "([^"]*)".*$#', $l, $m)) {
		#
		# Requested access log format out of lb:
		dbg("Requested access log format out of lb");
		[ $all, $stamp, $ip, $cmd, $api, $status ] = $m;
		po($stamp, $ip, $cmd, $api, $status);
	} else { 
		dbg("NOT matching ($l)");
	}
}
	

