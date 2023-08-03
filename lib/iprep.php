<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/curl.php");

class iprep {
	function __construct() {
		$this->tt = [  "isocode" => "isocode", "country" => "country", "state" => "state", "city" => "city", "discover_date" => "discover", "threat" => "threat", "risk_level" => "risk" ];
		$this->db        = false;
		$this->pxybypass = false;
		$this->debug     = false;
		$this->obsolete	 = 100;
		$cf = "conf/iprep.php";
		if (file_exists($cf)) {
			include($cf);
			if (isset($iprep_url))      $this->url       = $iprep_url;
			if (isset($iprep_hdr))      $this->hdr       = $iprep_hdr;
			if (isset($iprep_opts)) 	$this->opts      = $iprep_opts;
			if (isset($iprep_obsolete))	$this->obsolete  = $iprep_obsolete;
			if (isset($iprep_db)){
				if (isset($iprep_dbuser) && isset($iprep_dbpass)) 
					$this->db = new db($iprep_db, $iprep_dbuser, $iprep_dbpass);
				else
					$this->db = new db($iprep_db);
			}	
		}
	}
	private function lookup($ip) {
		$s = "select * from tech.iprep where ip = '$ip'";
		#dbg($s);
		$q = new query($this->db, $s);
		if ($q->nrows() == 0) return false;
		return $q->obj();
	}
	private function update($ip, $data, $ori = false) {
		if ($data == false) return;
		if ($ori == false) {
			$ori = $this->lookup($ip);
			if ($ori == false && is_object($data)) {
				$this->insert($ip, $data);
				return;
			}
		}	
		$set = [];
		array_push($set, "modified = now()");
		foreach ($this->tt as $k => $v) {
			$change = "";
			if (property_exists($data, $k)) {
				if ($data->$k != $ori->$v) {
					$nv = $data->$k;
					if ($v != "risk") $nv = "'$nv'";
					else {
						if ($data->$k < $ori->$v)       $change = "improved";
						else							$change = "worsened";
						array_push($set, "change = '$change'");
					}
					array_push($set, "$v = $nv");
				} else if ($v == 'risk') 
					array_push($set, "change = 'stable'");
			}
		}
		$s = "update tech.iprep set " . implode(", ", $set) . " where ip = '$ip'";
		#dbg("$s");
		new query($this->db, $s);
	}
	private function insert($ip, $data) {
		$cols = [];
		$vals = [];
		
		array_push($cols, "ip");
		array_push($vals, "'$ip'");

		if (!is_object($data)) return;

		foreach ($this->tt as $k => $v) {
			#dbg("k: $k, v: $v");
			if (property_exists($data, $k)) {
				array_push($cols, $v);
				if ($v == "risk") array_push($vals, $data->$k);
				else			  array_push($vals, "'". $data->$k ."'");
			} else dbg("not found");
		}	
		$s = "insert into tech.iprep (". implode(",", $cols) .") values (" . implode(",", $vals) .")";
		#dbg("$s");
		new query($this->db, $s);
	}
	function get_full($ip) {
return false;
		$c   = new curl($this->url, $this->hdr, $this->pxybypass, $this->debug, $this->opts);
		if (($r = $c->get("$ip")) === false) return false;
		return json_decode($r);
	}
	function get($ip) {
		if ($this->db !== false) {
			$data = $this->lookup($ip);
			if ($data !== false) {
				$d1 = new DateTime($data->modified);
				$d2 = new DateTime("now");
				$iv = $d1->diff($d2);
				$nd = $iv->days;
				if ($nd < $this->obsolete) return $data;
				#dbg("obsolete: $nd vs $this->obsolete");
			} 
		}
return false;
		$c   = new curl($this->url, $this->hdr, $this->pxybypass, $this->debug, $this->opts);
		if (($r = $c->get("$ip")) === false) return false;
	dbg($r);	
		$r = json_decode($r);
		if ($this->db) $this->update($ip, $r);

		$rr = (object)[];
		$rr->ip = $ip;

		foreach ($this->tt as $k => $v) {
			if (property_exists($r, $k)) $rr->$v = $r->$k;
			else						 $rr->$v = null;
		}
		return $rr;
	}
}
?>
