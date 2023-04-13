<?php
require_once("lib/dbg_tools.php");;

/** 
 * class to handle un a simple way incoming data (arguments) coming from GET, POST methors or application/json requests.
 */
class args {
	function __construct() {
		if (array_key_exists("CONTENT_TYPE", $_SERVER) && substr($_SERVER["CONTENT_TYPE"], 0, 16) == "application/json") {
			$b = file_get_contents('php://input');

			if (is_string($b))
				$a = json_decode($b);
			else $a = $b;
		
			if (is_array($a) || is_object($a)) foreach ($a as $k => $v) $_POST[$k] = $v;
		}
	}
	function err() {
		foreach ($_POST as $k => $v) 
			err("\$_POST['$k'] = '$v'");
		foreach ($_GET as $k => $v) 
			err("\$_GET['$k'] = '$v'");
	}
	function _dbg() { return dbg(); }
	function dbg() {
		foreach ($_POST as $k => $v) 
			dbg("\$_POST['$k'] = '$v'");
		foreach ($_GET as $k => $v) 
			dbg("\$_GET['$k'] = '$v'");
	}
	/**
     * Get parameter $p from $_POST if it exists
     */ 
	function post($p) {
		if (isset($_POST[$p])) return $_POST[$p];
		return false;
	}
	/**
     * Get parameter $p from $_GET if it exists
     */ 
	function get($p) {
		if (isset($_GET[$p])) return $_GET[$p];
		return false;
	}
	/**
     * Check if parameter $p exists in $_POST or $_GET
     */ 
	function has($p) {
		if (isset($_POST[$p])) return true;
		if (isset($_GET[$p] )) return true;
		return false;
	}
	/**
     * Get paramter $p from $_POST or $_GET if it exists
     */ 
	function val($p) {
		if (isset($_POST[$p])) return $_POST[$p];
		if (isset($_GET[$p] )) return $_GET[$p];
		return null;
	}
	/**
     * unset paramter $p from $_POST or $_GET if it exists
     */ 
	function clean($p) {
		if (isset($_POST[$p])) unset($_POST[$p]);
		if (isset($_GET[$p] )) unset($_GET[$p]);
	}
    /**
     * Get all parameters from $_POST and $_GET in one array:
     */
	function all_get() {
		return array_merge($_POST, $_GET);
	}
	/**
     * unset all parameters from $_POST and $_GET
     */ 
	function all_clean() {	
		foreach ($_POST as $k => $v) {
			unset($_POST[$k]);
		}
		foreach ($_GET as $k => $v) {
			unset($_GET[$k]);
		}
	}
};
?>
