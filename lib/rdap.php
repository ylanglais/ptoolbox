<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/curl.php");

class rdap {
	function __construct() {
		$this->db        = false;
		$this->host      = "http://rdap.net/";
		$this->hdr       = [ "Content-type: application/rdap+json" ];
		$this->pxybypass = false;
		$this->debug     = false;
		$cf = "conf/rdap.php";
		if (file_exists($cf)) {
			include($cf);
			if (isset($rdap_host))      $this->host      = $rdap_host;
			if (isset($rdap_hdr))       $this->hdr       = $rdap_hdr;
			if (isset($rdap_pxybypass)) $this->pxybypass = $rdap_pxybypass;
			if (isset($rdap_debug))     $this->debug     = $rdap_debug;
			if (isset($rdap_db))   {
				if (isset($rdap_dbuser) && isset($rdap_dbpass)) 
					$this->db   = new db($rdap_db, $rdap_dbuser, $rdap_dbpass);
				else
					$this->db   = new db($rdap_db);
			}
		}
	}
	private function _dbinsert($inetname, $name, $country, $minip, $maxip, $ipver, $type) {
		$s = "insert into  tech.rdap (inetnum, name, country, net_min_lg, net_max_lg, ipver, type) values ('$inetname', '$name', $country, $minip, $maxip, '$ipver', '$type')";
#dbg("$s");
		$q = new query($this->db, $s);
	}
	private function _dbupdate($inetname, $name, $country, $minip, $maxip) {
		$s = "update tech.rdap set name = '$name', country = $country, modified = now() where inetname = '$inetname'"; 
		$q = new query($this->db, $s);
	}
	private function _dblookup($ip) {
		$h = ip2long($ip);
		$s = "select * from tech.rdap where $h >= net_min_lg and $h <= net_max_lg";
		$q = new query($this->db, $s);
		$c = $q->nrows();
		if ($c == 0) 
			return false;	
		# if ($c > 1) err("conflicting rdap data for $ip"); 
		return $q->obj();
	}
	private function whois($ip) {
		exec("whois $ip | egrep -i 'inetnum:|netname:|status:|country:'", $out, $xit);
		if ($xit != 0 || count($out) == 0) return false;
		$data = (object) [];
		foreach ($out as $o) {
			if (preg_match("/([a-zA-Z-]*):[ 	]*(.*)$/",$o, $m)) {
				if (strtolower($m[1]) == "inetnum") $data->handle  = $m[2];
				if (strtolower($m[1]) == "netname") $data->name    = $m[2];
				if (strtolower($m[1]) == "status")  $data->type    = $m[2];
				if (strtolower($m[1]) == "country") $data->country = $m[2];


				if (strtolower($m[1]) == "org-name") $m[1] = "orgname";
				$data->{strtolower($m[1])} = strtolower($m[2]);
				if (strtolower($m[1]) == "inetnum") {
					$ipr = strtolower($m[2]);
					if (preg_match("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})[ 	]*-[ 	]*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", $ipr, $p)) {
						dbg("1: $p[1], 2: $p[2]");
						$data->startAddress = $p[1];
						$data->endAddress   = $p[2];
						$data->ipVersion    = "v4";
					} else {
						err("bad inetnum format: $ipr, expecting [0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}[ 	]*-)[ 	]*[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}");
					}
				}
			} else {
				err("bad line: $o");
			} 
		}
		return $data;
	}
	private function _get($ip) {
		$c  = new curl($this->host, $this->hdr, $this->pxybypass, $this->debug);
		$r = $c->get("ip/$ip");
		if ($r === false) {
			$r = $this->whois($ip);
			if ($r === false) {
				warn("no return / bad ip $ip");
				return false;
			}
		}
		return json_decode($r);
	}
	private function _extract_vcard_data($r) {
		$name = "";
		foreach ($r->entities as $en => $ev) {
			if ($ev->roles[0] == "administrative") {
				#dbg("> " .json_encode($ev->vcardArray[0]));
				foreach ($ev->vcardArray[1] as $a) {	
					#dbg("-> $a[0] ---> $a[3]");
					if ($a[0] == "kind") $name .= "$a[3] - ";
					if ($a[0] == "fn")   {
						return $name . "$a[3]";
					}
				}
			}
		}
		return $name;
	}
	function get_full($ip) {
		$c  = new curl($this->host, $this->hdr, $this->pxybypass, $this->debug);
		$r = $c->get("ip/$ip");
		return json_decode($r);
	}
	function get($ip) {
		if ($this->db != false) {
			$l = $this-> _dblookup($ip);
			if ($l !== false) {
				$a = (object) [];
				$a->name = $l->name;
				$a->country = $l->country;
				$a->type    = $l->type;
				$a->comment = $l->comment;
				$minip      = long2ip($l->net_min_lg);
				$maxip      = long2ip($l->net_max_lg);
				return $a;
			}
		}
		$r = $this->_get($ip);
		if ($r === false || $r === null) return false;
		if ($this->db !== false ) {
			if (property_exists($r, "handle") && property_exists($r, "startAddress") && property_exists($r, "endAddress")) {
				if ($r->startAddress == "0.0.0.0") $r = $this->whois($ip);

				if ($r->ipVersion == "v6") {
					$minip = ip2long_v6($r->startAddress);
					$maxip = ip2long_v6($r->endAddress);
				} else {
					$minip = ip2long($r->startAddress);
					$maxip = ip2long($r->endAddress);
				}
				if (property_exists($r, "country")) $country = "'$r->country'";
				else                                $country = "null";
				if (property_exists($r, "name")) { 
					$name = $r->name;
				} else {
					$name = $this->_extract_vcard_data($r);
					if ($name == "") {
						if ($country != "null") $name = $r->handle; 
						else return $r;
					}
					$r->name = $name;
				}

				if ($minip == 0) {
					err("$ip gets 0.0.0.0 - 255.255.255.255 IANA-BLOCK");
					return $r;
				}
				$this->_dbinsert($r->handle, $name, $country, $minip, $maxip, $r->ipVersion, $r->type);
			}
		}
		return $r;
	}
}
