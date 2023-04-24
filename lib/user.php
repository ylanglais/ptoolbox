<?php
require_once("lib/query.php");
require_once("lib/audit.php");
require_once("lib/date_util.php");
require_once("lib/dbg_tools.php");
require_once("lib/util.php");
require_once("lib/auth_local.php");
require_once("lib/auth_ldap.php");

class user {
	private $id;
	private $login;
	private $ip;
	private $profile;
	private $passwd;
	private $active;
	private $since;
	private $until;
	private $roles;
	private $rights;

	//public $session;

	function __construct() { 
		$this->user_table      = "tech.user";
		$this->role_table      = "role";
		$this->user_role_table = "usr_rol";
		$this->user_id         = "usr_id";
		$this->role_id		   = "rol_id";
		$this->rights = [];

		if (file_exists("conf/user.php")) {
			include("conf/user.php");
			$this->user_table      = $user_table;
			$this->role_table      = $role_table;
			$this->user_role_table = $user_role_table;
			if (isset($user_id)) $this->user_id         = $user_id;
			else                 $this->user_id         = "user_id";
			if (isset($role_id)) $this->role_id		    = $role_id;
			else                 $this->role_id         = "role_id";
		}
	}

	function set($usr_login, $usr_profile, $usr_active) {
		$this->login   = $usr_login;
		$this->profile = $usr_profile;
		$this->active  = $usr_active;
	}
	function dbg() {
		foreach (get_class_vars("address") as $k => $v)	
			dbg_html($k . ": " . eval("return \$this->$k;"));
	}
	function id() {
		return $this->id;
	}
	function roles() {
		return $this->roles;
	}
	function has_role($role) {
		if ($this->roles == null || !in_array($role, $this->roles)) return false;
		return true;
	}
	function login($login = "") {
		if ($login != "") $this->login = $login;
		return $this->login;
	}
	function passwd($passwd = "") {
		if ($passwd != "") $this->passwd = $passwd;
		return $this->passwd;
	}
	function profile($profile = "") {
		if ($profile != "") $this->profile = $profile;
		return $this->profile;
	}
	function since($since = "") {
		if ($since != "") $this->since = $since;
		return $this->since;
	}
	function until($until = "") {
		if ($until != "") $this->until = $until;
		return $this->until;
	}
	function active($active = "") {
		if ($active == "Y" || $active == "N") 
			$this->active = $active;
		return $this->active;
	}
	function load_roles() {
		$q =  new query("SELECT name from $this->role_table where id in (select $this->role_id from $this->user_role_table where $this->user_id = '$this->id')");
		$this->roles = [];
		while ($row = $q->data()) {
			if ($row['name'] == "local" && $this->source != "local") next;
			array_push($this->roles, $row['name']);
		}
		if ($this->source == "local" && !in_array("local", $this->roles)) array_push($this->roles, "local");
	}
	function load_rights() {
		dbg("SELECT type, link, perm from param.right where role_id in (select $this->role_id from $this->user_role_table where $this->user_id = '$this->id')");
		$q =  new query("SELECT type, link, perm from param.right where role_id in (select $this->role_id from $this->user_role_table where $this->user_id = '$this->id')");
		$tab = [];
		$ent = [];
		while ($o = $q->obj()) {
			if      ($o->type == 'table')  $tab[$o->link] = $o->perm;
			else if ($o->type == 'entity') $ent[$o->link] = $o->perm;
		}
		$q =  new query("SELECT type, link, perm from param.right where user_id = $this->id");
		while ($o = $q->obj()) {
			if      ($o->type == 'table')  $tab[$o->link] = $o->perm;
			else if ($o->type == 'entity') $ent[$o->link] = $o->perm;
		}
		$this->rights["table"]  = $tab;
		$this->rights["entity"] = $ent;
	}
	function right_on($type, $link) {
		if (is_array($this->rights) && array_key_exists($type, $this->rights) && array_key_exists($link, $this->rights[$type])) {
			return $this->rights[$type][$link];
		}
		return 'NONE';
	}
	function auth_check($login, $passwd, $ip) {
		if ($login == "admin") {
			$auth = new auth_local();
			if (!$auth->check($login, $passwd)) {
				audit_login_error($login, $ip, 'bad_admin_pass');
				return false;
			}
			$this->id       = 0;
			$this->login    = "admin";
			$this->profile  = "admin";
			$this->active   = "Y";
			$this->since    = "1970-01-01";
			$this->until    = "";
			$this->load_roles();
			$this->load_rights();
			array_push($this->roles, "local");
			return true;
		} else {
			$auth = new auth_ldap();
			if ($auth->check($login, $passwd)) {
				$q = new query("SELECT * from $this->user_table where login = :login", [":login" => $login] );
				if ($q->nrows() < 1) {
					$q = new query("select max(id)+1 as id from $this->user_table");
					$o = $q->obj();
					if ($o->id > 10) $id = $o->id;
					else $id = 10;
					$now = today('');
					new query("insert into $this->user_table (id, login, since, active) values ($id, '$login', '$now', 'Y')");
					$q = new query("SELECT * from $this->user_table where login = :login", [":login" => $login] );
				} 
				$dat = $q->data();
			
				// set members w/ data:
				foreach ($dat as $k => $v) {
					$this->$k = $v;
					//eval("\$this->$k = \"$v\";");
				}
				$this->source = "ldap";
				$this->load_roles();
				$this->load_rights();
				
				return true;
			} else { _warn("ldap not matching"); }

			# test local auth:
			$auth = new auth_local();
			if ($auth->check($login, $passwd)) {
				$q = new query("SELECT * from $this->user_table where login = :login and passwd is not null", [":login" => "$login"] );
				if (($dat = $q->data()) == null) {
					audit_login_error($login, $ip, 'bad_user');
					return false;
				}
				if ($dat['active'] != "Y") {
					#dbg_html("user inactive pass");
					audit_login_error($login, $ip, 'user_inactive');
					return false;
				}
				// set members w/ data:
				foreach ($dat as $k => $v) {
					eval("\$this->$k = '$v';");
				}

				$now = today('');

				if ($this->until != "" && $thist->until < $now) { 
					// need to desactivate user:
					audit_login_error($login, $ip, 'user_obsolete');
					new query("update $this->user_table set active = 'N' where login = :login", [ ":login" => "$login" ]);
					return false;
				}
			
				if ($this->since != "" && $this->since > $now) {
					#dbg_html("user not active yet");
					audit_login_error($login, $ip, 'user_not_activated');
					return false;
				}

				$this->source = "local";
				$this->load_roles();
				return true;
			} 
		} 
		return false;
	}	
	function load($login = "") {
		#echo "<pre>\$user->load: SELECT * from $this->user_table where login = \"$login\"</pre>";
		if ($login == "" && $this->login != "") $login = $this->login;
		if (!($q = new query("SELECT * from $this->user_table where login = :login", [ ":login" => $login ]))) return false;
		if ($q->nrows() < 1) return false;
		foreach (($row = $q->data()) as $k => $v) {
			eval("\$this->$k = \"$v\";");
		}
		if ($this->login == "") return false;

		$this->load_roles();

		return true;
	}
	function activate($login) {
		return new query("update $this->user_table set active = 'n' where login = :login", [ ":login" => $login ]);
	}
	function deactivate() {
		return new query("update $this->user_table set active = 'y' where login = :login", [ ":login" => $login ]);
	}
};
?>
