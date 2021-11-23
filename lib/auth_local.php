<?php
require_once("lib/query.php");
require_once("lib/util.php");

/**
 * Simple local auth class to check login/passwd pair w/o ldap admin account
 */
class auth_local {
	private $cost;
	private $algo;

	/** 
	 * Initialize local auth data
	 */ 
	function __construct() {
		$this->cost = 25000;
		$this->algo = "sha512";
		if (file_exists("conf/user.php")) {
			include("conf/user.php");
			$this->user_table      = $user_table;
			$this->role_table      = $role_table;
			$this->user_role_table = $user_role_table;
			$this->user_id         = $user_id;
			$this->role_id		   = $role_id;
		}
	}

	/**
     * Generate random salt
	 * @param $len 		size of the salt string (default 23)
	 * @return 	a random salt string base64 encoded
	 */
	function salt($len = 23) {
		return substr(base64_encode(openssl_random_pseudo_bytes(64)),0, $len);
	}
	
	/** 
     * Generate pbkdf2 hash
	 * @param $algo		hash algorithm to be used
	 * @param $pass		string to be hashed
	 * @param $salt		salt string
	 * @param $cost		cost number 
	 * @return the hashed $pass string
     */
	function pbkdf2($algo, $pass, $salt, $cost) {
		return base64_encode(hash_pbkdf2($algo, $pass, $salt, $cost, 0, true));
	}

	/** 
	 * Generate a db passwd string from clear passwd
	 * @param $pass		the password tp be hased
	 * @return	a complete hash string, including algo, salt cost and hash to be stored in db
	 */
	function dbstr_from_pass($pass) {
		$algo = $this->algo;
		$cost = $this->cost;
		$salt = $this->salt();
		$hash = $this->pbkdf2($algo, $pass, $salt, $cost);
		return "\$pbkdf2-$algo\$$cost\$$salt\$$hash";
	}

	/** 
	 * method to check if a given login string is not a potential injection
	 * @param login		the string to verify
	 * @return false if the string is longer than 30chars or contains quote or parentheses<br/>
     *         true  if the string is ok
	 */
	private function verif_login($login) {
		if (strlen($login) > 30 || strstr("'", $login) || strstr("(", $login) || strstr(")", $login)) {
			$ip = get_ip() || "no ip";
			return "Inadequate login from $ip <<$login>>";
		}
		return true;
	}

	/**
     * Check credentials 
	 *	@param $login	a user login
	 *  @param $passwd	a user password
	 *  @return true if le login/passwd pair matches a user in the local database<br/>
	 *			false if not
	 */
	function check(string $login, string $passwd) :bool {
		#
		# Check that login is not a kind of injection (length < 30, no quote, no parentheses); 
		if (($v = $this->verif_login($login)) !== true) {
			_err($v);
			return false;
		}
		
		#
		# Use se secure query:
		$q = new query("select passwd from $this->user_table where login = :login", [ ":login" => $login ]);

		#
		# No data retrieved:
		if ($q->nrows() < 1) {
			_err("bad login: '$login'");
			return false;
		} 

		#
		# Extract algo, cost, salt and hash:
		$r = $q->obj();
		if (!preg_match('/^\$pbkdf2-([^$]*)\$([0-9]*)\$([^\$]*)\$(.*)$/', $r->passwd, $m)) {
			_err("bad password structure!!!");
			return false;
		}	
		$algo = $m[1];
		$cost = $m[2];
		$salt = $m[3];
		$hash = $m[4];
		
		#
		# Recompute hash from passwd w/ appropriate data:
		$h = $this->pbkdf2($algo, $passwd, $salt, $cost);

		#
		# check passwd:
		if ($h == $hash) return true;

		_err("invalid passwd for user $login");
		return false; 
	}
        
	/**
     * Method to update password of a given login.
	 * @param $login	the login
	 * @param $pass		the new password
	 * @return false if login is bad (potential injection) or if the update fails<br/>
	 *		   true  if password changed in db
	 */
	function update(string $login, string $pass) :bool {
		#
		# Check that login is not a kind of injection (length < 30, no quote, no parentheses); 
		if (($v = $this->verif_login($login)) !== true) {
			_err($v);
			return false;
		}
		$hash = $this->dbstr_from_pass($pass);
		$q = new query("update $this->user_table set passwd = :hash where login = :login", [ ":login" => $login, ":hash" => $hash ] );
		if (($e = $q->err()) !== false) {
			_err("$e");
			return false;
		}
		return true;
	}

/*
	TODO: check what to do about that
	function check_token($pwd_db, $passwd) {
		if (!preg_match('/^\$pbkdf2-([^$]*)\$([0-9]*)\$([^\$]*)\$(.*)$/', $pwd_db, $m))
                    return false;
			
		$algo = $m[1];
		$cost = $m[2];
		$salt = $m[3];
		$hash = $m[4];
		
		$h = $this->pbkdf2($algo, $passwd, $salt, $cost);
		if ($h == $hash) return true;
		return false; 
	}
*/
}
?>
