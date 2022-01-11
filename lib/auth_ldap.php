<?php
require_once("lib/dbg_tools.php");

/**
  * Simple ldap auth class to check login/passwd pair w/o ldap admin account
  */
class auth_ldap {
	/** 
     * Constructor reads conf/ldap.php config file and connects to server uing config file data
	 * No permission data is retreived from LDAP server.
     * Interface could be simpler since normally ldap auth is only used once in an http request.
	 * For more info about config file, please read config file template comments.
     */
	function __construct() {
		$this->ldap = false;
		$ldap_ver = 3;
		$this->timeout = 1;
		if (file_exists("conf/auth_ldap.php")) {
			include("conf/auth_ldap.php");
			if (!isset($ldap_srv)) {
				info("no ldap_server defined");
				return;
			}
			if (isset($ldap_timeout) && is_int($ldap_timeout) && $ldap_timeout > 0 && $ldap_timeout < 10) {
				$this->timeout = $ldap_timeout;
			}

			if (!is_array($ldap_srv)) $ldap_server = [$ldap_srv];
			if (isset($ldap_domain) && isset($ldap_uid))
				$this->rstr = "$ldap_uid=%s,$ldap_domain";
			else if (isset($ldap_fqdn)) 
				$this->rstr = "%s@$ldap_fqdn";
				
			else 
				$this->rstr = "%s";
	
			foreach ($ldap_srv as $srv) {
err($srv);
				if ($this->_ping($srv) && ($this->ldap = ldap_connect($srv)) !== false) break;
			}
			if ($this->ldap === false) {
				err("not connect to a ldap server");
				return;
			}
			
			ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, $ldap_ver);
			ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

			if (isset($ldap_opts) && is_array($ldap_opts)) {
				foreach ($ldap_opts as $k => $v) {
					try {
						ldap_set_option($this->ldap, $k, $v);
					} catch (Exception $e) {
						warn("ignoring invalid ldap option value pair [ $k => $v ]");
					}
				}
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

	/** 
	 * Destructor only closes connection to ldap sever
     */
	function __destruct() {
		if ($this->ldap) {
			ldap_close($this->ldap);
			$this->ldap = false;
		}
	}
	/** 
     * Check pour matching $login/$passwd pairs in the LDAP database.
	 * @param $login	login string
	 * @param $passwd	clear password string
	 * @return If ldap connecion is false, $login or $passwd are empty, null, false or not matching => return false. Else true is returned
     */
	function check($login, $passwd) {
		if ($this->ldap === false) return null;
		if ($login  == "" || $login  == NULL || $login  === false) return false;
		if ($passwd == "" || $passwd == NULL || $passwd === false) return false;
		if ($this->ldap) {
			$req = sprintf($this->rstr, $login);
			if (@ldap_bind($this->ldap, $req, $passwd)) return true;
			return false;
		}
		return null;
	}
}
?>
