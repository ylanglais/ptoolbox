<?php
require_once("lib/dbg_tools.php");

/**
 * Simple session caching data
 */
class store {
	const STORE = "_s_t_o_r_e_" ;

	/**
	 * Put key/value pair into the storage
	 */
	static function put($key, $val) {
		if (!isset($_SESSION)) {
			#err("no session to store whatsoever");
			return false;
		}
		if (!array_key_exists(self::STORE, $_SESSION)) 
			$_SESSION[self::STORE] = [];
		$_SESSION[self::STORE][$key] = $val;
	 
	}

	/**
	 * Get value of key from the storage
	 */
	static function get($key) {
		if (!isset($_SESSION) ||
		    !array_key_exists(self::STORE, $_SESSION) || 
		    !array_key_exists($key, $_SESSION[self::STORE])) 
			return false;
		return $_SESSION[self::STORE][$key];
	}

	/**
	 * Wipe storage
	 */
	static function wipe() {
		if (isset($_SESSION) && 
			array_key_exists(self::STORE, $_SESSION))
		unset($_SESSION[self::STORE]);
	}

	/**
	 * retrieve the whole datastorage in a json string:
	 */
	static function dump() {
		if (!isset($_SESSION) ||
		    !array_key_exists(self::STORE, $_SESSION))
			return false;
		return json_encode($_SESSION[self::STORE]); 
	}
	/**
	 * Restore json string content has storage
	 */
	static function restore($jsstore) {
		if (!isset($_SESSION)) return false;
		$_SESSION[self::STORE] = json_decode($jsstore);
		return true;
	}
}
