<?php
require_once("lib/dbg_tools.php");

class ad {
	function __construct() {
		$this->ad    = false;
		$this->base    = false;
		$this->filter  = false;
		$this->timeout = 1;
		$ad_ver = 3;

		if (file_exists("conf/ad.php")) {
			include("conf/ad.php");
			if (!isset($ad_srv)) {
				info("no ad_srv defined");
				return;
			}
			if (isset($ad_timeout) && is_int($ad_timeout) && $ad_timeout > 0 && $ad_timeout < 10) {
				$this->timeout = $ad_timeout;
			}

			if (!is_array($ad_srv)) $ad_server = [$ad_srv];

			if (isset($ad_fqdn)) $this->fqdn = $ad_fqdn;
			if (isset($ad_base)) $this->base = $ad_base;


			if (isset($ad_domain) && isset($ad_uid))
				$this->rstr = "$ad_uid=%s,$ad_domain";
			else if (isset($ad_fqdn)) 
				$this->rstr = "%s@$ad_fqdn";
				
			else 
				$this->rstr = "%s";
	
			foreach ($ad_srv as $srv) {
				if ($this->_ping($srv) && ($this->ad = ldap_connect($srv)) !== false) break;
			}
			if ($this->ad === false) {
				err("not connect to a ad server");
				return;
			}
			
			ldap_set_option($this->ad, LDAP_OPT_PROTOCOL_VERSION, $ad_ver);
			ldap_set_option($this->ad, LDAP_OPT_REFERRALS, 0);

			if (isset($ad_base))      $this->base      = $ad_base;
			if (isset($ad_filter))    $this->filter    = $ad_filter;
			if (isset($ad_attribute)) $this->attribute = $ad_attribute;

			if (isset($ad_opts) && is_array($ad_opts)) {
				foreach ($ad_opts as $k => $v) {
					try {
						ldap_set_option($this->ad, $k, $v);
					} catch (Exception $e) {
						warn("ignoring invalid ad option value pair [ $k => $v ]");
					}
				}
			}
			if (!ldap_bind($this->ad, $ad_user, $ad_pass)) {
				err("bad login/pass");
				return;
			}
		} 
	}
	function _ping($srv) {
		if (($n = preg_match("/(ldap[s]?):\/\/([^:]*)(:([0-9]*))?$/", $srv, $m)) === false) return false;
		if ($n === false) return false;
		$p = 0;
		if ($n < 5) {
			if ($m[1] == "ldap")       $p = 389;
			else if ($m[1] == "ldaps") $p = 636;
			else return false;
		} else {
			$p = $m[4];
		}
		
		$s = $m[2];
		
        $op = fsockopen($s, $p, $errno, $errstr, $this->timeout);
        if (!$op) return false;
		fclose($op);
		return true;
	}
	function user_groups2($login) {
		if ($this->ad === false || $this->base === false || $this->filter === false) return null;
		if ($this->attribute === false) $this->attribute = 'memberof'; 
		$r = ldap_search($this->ad, $this->base, "($this->filter$login)", [ $this->attribute ]);

		$groups = [];

		if ($r === false) return false;
		$i = ldap_get_entries($this->ad, $r);
		
		print_r($i); print("\n");


/**
		if (array_key_exists($this->attribute, $i[0]) && is_array($i[0][$this->attribute])) {
				foreach ($i[0][$this->attribute] as $j => $g) {
					if (is_string($j))  continue;
					array_push($groups, $g);
				}
			}
		}
*/
		return $groups;
	}
	function user_groups($login) {
		if ($this->ad === false || $this->base === false || $this->filter === false) return null;
		if ($this->attribute === false) $this->attribute = 'memberof'; 
		$r = ldap_search($this->ad, $this->base, "($this->filter$login)", [ $this->attribute ]);

		$groups = [];

		if ($r === false) return false;
		$i = ldap_get_entries($this->ad, $r);
		if (is_array($i) && is_array($i[0])) {
		if (array_key_exists($this->attribute, $i[0]) && is_array($i[0][$this->attribute])) {
				foreach ($i[0][$this->attribute] as $j => $g) {
					if (is_string($j))  continue;
					array_push($groups, $g);
				}
			}
		}
		return $groups;
	}
	function search2($filter, $attrs = []) {
		$r = ldap_search($this->ad, $this->base, $filter, $attrs);
		if ($r === false) return false;
		$e = ldap_get_entries($this->ad, $r);
		return $e;	
	}

	function add_array(&$data, $e) {
		if (array_key_exists("count", $e)) {
			$ne = $e["count"];
			$data = (object)[];
			for ($i = 0; $i < $ne; $i++) {
				$k = $e[$i];
				if ($e[$k]["count"] == 1) {
					$data->$k = $e[$k][0];
				} else {
					$data->$k = [];
					for ($j = 0; $j < $e[$k]["count"]; $j++) {
						if (is_array($e[$k][$j])) {
							$this->add_array($data->$k[$j], $e[$k][$j]);
						} else {
							$data->$k[$j] = $e[$k][$j];
						}
					}
				}
			}
		}
	}

	function search($filter, $attrs = []) {
		$r = ldap_search($this->ad, $this->base, $filter, $attrs);
		if ($r === false) return false;
		
		$es = ldap_get_entries($this->ad, $r);

		$data = [];

		for ($ie = 0; $ie < $es["count"]; $ie++) {
			$e = $es[$ie];
			#$this->add_array($data[$ie], $e);
			$data[$ie] = (object)[];
			$ne = $e["count"];
			for ($i = 0; $i < $ne; $i++) {
				$k = $e[$i];
				if ($e[$k]["count"] == 1) {
					$data[$ie]->$k = $e[$k][0];
				} else {
					$data[$ie]->$k = [];
					 
					for ($j = 0; $j < $e[$k]["count"]; $j++) {
						$data[$ie]->$k[$j] = $e[$k][$j];
					}
				}
			}
		}
		return $data;
	}

}
