<?php
require_once("lib/db.php");
require_once("lib/user.php");
require_once("lib/audit.php");
require_once("lib/args.php");

function session_prjnomcmp($a, $b) {
	return strcmp($a->nom, $b->nom);
}	
	
class session {
	private $sid;	
	private $name;
	public  $user;
	public  $login;	
	public  $profile;
	public  $page;
	public  $prev;
	public  $userdata;
	public  $roles;

	function __construct() {
		if (!isset($_SESSION)) session_start(); 
		#
		# restore session data:
	 	foreach (get_class_vars("session") as $k => $v ) {
			if ($k != "db") {
				if (isset($_SESSION[$k])) {
					$this->$k = $_SESSION[$k];
					#_err("$k => " . json_encode( $_SESSION[$k]));
				} #else _log("session: $k is empty");
			}
		}
		$args = new args();
        #
        # update page trackgin:
        if ($args->has('page')) {
            $_SESSION['prev'] = $this->prev = $this->page;
            $_SESSION['page'] = $this->page = $args->val('page');
        }

		if (file_exists("usr/usrsession.php")) {
			include_once("usr/usrsession.php");
			usr_session($this);
		}

	}
	function id() {
		return $this->sid;
	}
	function check() {
		if ($this->isnew()) { echo("<script>window.location.href='index.php'</script>"); exit;}
		return true;
	}
	function isnew() {
		if (isset($_SESSION['login'])) return false;
		return true;
	}
	function userdata($data = null) {
		if ($data == null) {
			return $this->usrdata;
		}
		$this->usrdata = $data;
	}
	function create($user) {
		#_log("create session");
		#session_regenerate_id();
		$this->ip   = $_SERVER['REMOTE_ADDR'];
		$this->name = $user->login() . "@" . $this->ip;
		#session_name($this->name);
		$this->sid   = session_id(); 

		audit_login($this->sid, $user->login(), $this->ip);

		$this->user     = $user;
		$this->login    = $user->login();
		$this->profile  = $user->profile();
		$this->roles    = $user->roles();

		if (file_exists("usr/usrsession.php")) {
			if (include_once("usr/usrsession.php")) {
				$this->usrdata = new usrsession($this);
			} else {
				_err("cannot include usr/usrsession.php");
			}
		}
		$this->cache_session_data();
	}
	function cache_session_data() {
		foreach (get_class_vars("session") as $k => $v ) {
			if ($k != "db") {
				$_SESSION[$k] = $this->$k;
			#	_err("session: $k => " . serialize($this->$k));
			}
		}
	}
	function has_role($role) {
		if ($this->roles == null || !in_array($role, $this->roles)) return false;
		return true;
	}
	function has($key) {
		if (isset($_SESSION[$key])) { 
			return true; 
		}
		return false;
	}
	function pushvar($key, $val) {
		$_SESSION[$key] = $val;
	}
	function getvar($key) {
		if (key_exists($key, $_SESSION)) {
			return $_SESSION[$key];
		}
		return false;	
	}
	function popvar($key) {
		$r = null;
		if ($this->has($key)) { 
			$r = $_SESSION[$key];
			unset($_SESSION[$key]);
		}
		return $r;
	}
	function destroy() {
		#include "debug.php";
		audit_logout($this->sid);
		session_unset();
		session_destroy();
		//session_regenerate_id(true);
	}
	function page($name ="") {
		if ($name <> "") $_SESSION['page'] = $this->page = $name;
		return $this->page;
	}
}
?>
