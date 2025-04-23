<?php

class albstats {
	function __construct($type) {
	
	}

	function expand_vars($vals, $v) {
		while (preg_match("/var\(([^\)]*)\)/", $v, $m)) {
			if (array_key_exists($m[1], $vals)) {
				$repl = $vals[$m[1]; 
				$v = preg_replace($m[0], $repl, $v)
			} else {
				err("no value found for $m[1] in " . json_encode($vals)}
				return $v;
			}
		}
		while (preg_match("/this\(([^\)]*)\)/", $v, $m)) {
			if (array_key_exists($m[1], $vals)) {
				$repl = $vals[$m[1]; 
				$v = preg_replace($m[0], $repl, $v)
			} else {
				err("no value found for $m[1] in " . json_encode($vals)}
				return $v;
			}
		}
		return $v;
	}
	
	function import_file($filename, $update_adhip = false) {
		if (!file_exists($filename)) {
			err("'$filename': nos such file");
			return;
		}
		if (substr($filename, -3) == '.gz') {
			exec("gunzip $filename");
			$filename = substr($filename, 0, -3);
		}
		$cksum = md5_file($filename);
#dbg("cksum $filename = $cksum");
		$q = new query("select * from espp.files where cksum = '$cksum' order by start_import");
		if ($q->nrows() >= 1) {
			err("file $filename has already been imported (md5sum: $cksum");
			return;
		}

		$istart = dbstamp();

		$ope = 'fopen';
		$gts = 'fgets';
		$clo = 'fclose';
		$eof = 'feof';

		if (substr($filename, -3) == '.gz') {
			$ope = 'gzopen';
			$gts = 'gzgets';
			$clo = 'gzclose';
			$eof = 'gzeof';
		}
		$f = $ope($filename, 'r');
		$fromdate = "";
		$todate   = "";

		$nline = 0;
		$logln = 0;
		$badln = 0;
		$stime = false;
		$etime = false;

$this->rexps = [
	"re"     => '#^.*| ([0-9]*)/([0-9]*)/([0-9]*) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])\.([0-9]*) ([^ ]*) "[^"]*" [0-9]* [0-9]* "[^"]*" "[^"]*$"',
	"fields" => [ "Y", "M", "D","h", "m", "s", "u", "ip"],
	"cols"   => [ "name" => "this(%name)", "stamp" => "var(%Y)-var(%M)", "nb", "unique(%ip)" ]
];

		$res = [];

		while (!$eof($f) && ($l = chop($gts($f))) != "") {
			$nline++;
			$nre = -1;
			for ($this->regexps as $r) {
				$nre++;
				if (preg_match($r->re, $l, $m)) {
					$mat  = [];
					$vals = [];

					foreach ($m as $n => $var) {
						$vals[$f->fields][$n]] = $m[$n];
					}
					if (!isset($res[$l])) $res[$l] = [];	

					foreach ($f->cols as $k => $v) {
						if ($v == "nb") {
							$res[$l][$k]++;
						} else {
							$this->expand_vars($vals, $v);
							
						

					$res[$l]
					
				}


			}


		}		
		$clo($f);
		new query("insert into stats.albfiles values ('$cksum', $nline, $logln, $badln, '$stime', '$etime', '$istart', now(), 'log')");
		#if (substr($filename, -3) != '.gz') {
		#	dbg("recompress/compress file");
		#	exec("gzip $filename");
		#}
		if ($update_adhip == true) {
			dbg("update adhip on period $stime to $etime");
			$this->update_adhip($stime, $etime);
		}


	}


}
?>
