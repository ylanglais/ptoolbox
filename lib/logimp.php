<?php
require_once("lib/dbg_tools.php");
require_once("lib/prov.php");

/*
 * Note on fields (flds) => {"0": "match_all", "1": "1st_match",...} || {"1": "1st_match", "2": "2nd_match", ...}
 */
 
class logimp {
	function __construct($type, $log = false) {
		$this->filters = false;
		$this->type    = $type;
		$p = new prov("db", "default.param.logfile");
		$this->filters = $p->get(["type" => $type]);	
		if (!$this->filters) {
			err("nunknown log file type $type");
			return;
		} 
		if (is_string($this->filters)) $this->filters = json_decode($this->filters);
		$this->prov = [];
	}

	function import($filename) {
		if ($this->filters === false) return;

		if (!file_exists($filename)) {
			err("$filename: nos such file");
			return;
		}
		$cksum = md5_file($filename);


		$p = new prov("db", "default.espp.files");

		$q = $p->get(["type" => $this->type, "cksum" => $cksum]);
		if ($q !== false && $q != []) {
			err("file $filename has already been imported as $this->type (md5sum: $cksum");
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
		$stime = null;
		$etime = null;

		while (!$eof($f) && ($l = chop($gts($f))) != "") {
			$nline++;
			$ok = false;

			foreach ($this->filters as $d) {
				#dbg($d);
				if (preg_match($d->regexp, $l, $m)) {
					if (!array_key_exists($d->destlnk, $this->prov)) $this->prov[$d->destlnk] = new prov("db", $d->destlnk);

					$kv = [];
					$flds = json_decode($d->flds);
					foreach ($flds as $num => $fld) $kv[$fld] = $m[$num];
					if (array_key_exists("stamp", $kv)) {
						if ($stime == null) $stime = $kv["stamp"];
						$etime = $kv["stamp"];
					}
					if (($r = $this->prov[$d->destlnk]->put($kv)) !== true) err($r);
					$logln++;
					$ok = true;
					break;
				}
			}
			if (!$ok) { 
				$badln++;
				if ($log === true) err("bad line: $l");
			}
		}
		$p->put([
			"cksum"        => $cksum, 
			"in_lines"     => $nline, 
			"log_lines"    => $logln, 
			"bad_lines"    => $badln, 
			"start_stamp"  => $stime, 
			"end_stamp"    => $etime, 
			"start_import" => $istart, 
			"end_import"   => dbstamp(), 
			"type"         => $this->type]); 

		#new query("insert into espp.files values ('$cksum', $nline, $logln, $badln, '$stime', '$etime', '$istart', now(), 'audit')");

	}
}

?>
