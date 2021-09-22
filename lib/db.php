<?php
require_once("lib/dbg_tools.php");

 /** 
  * <h1>Simple database connection based on PDO</h1>
  * <p> 
  * This class is meant to facilitate db connection. Most of the time, within a given application, this class 
  * is not of a great use since the query class encapsulate connection and deals with low level stuff using the
  * default application database configured in conf/db.php
  * </p><p>
  * However, for more complex applications that connect to multiple databases, this class become required. 
  * </p><p>
  * It is also a very usefull class in order to look for meta information such as:
  * - databases accessible by the account on the dataserver
  * - schemas accessible by the account in the current database
  * - tables in the database/schema
  * - columns of a table/schema
  * - primary and secondary keys of a table/schema
  * - foreign keys of a table/schema
  * </p>
  *
  * @see query
  *
  * <h2>Examples:</h2>
  * <h3>1. Simple query w/ implicit database connection:</h3>
  * <pre>
  * require_once("lib/query.php");
  * $q = new query("select passwd from user where login= :login", [":login" => $login] );
  * if (($es = $q->err()) !== false) {
  *     print("kaboom : $es");
  * } else {
  *     $n = $q->nrows();
  *     if ($n < 1)      print("no user with this login ('$login')\n");
  *     else if ($n > 1) print("How wierd! several users with the same login (might wanna add a uniqueness constraint)!!!\n"); 
  *     else { 
  *        $o = $q->obj();
  *        if (!is_object($o)) print("bad luck!\n");
  *        else if (!property_exists($o, "passwd")) print("$login has no passwd, try again\n");
  *        print("$login's passwd is $o->passwd\n");
  *     }
  * }
  * </pre>
  * 
  * <h3>2. Explicit use of a connection to the defaut db</h3>
  * <pre>
  * require_once("lib/db.php");
  * require_once("lib/query.php");
  * $db = new db(); 
  * # or its equivalent:
  * # $db = new db("default");
  * $q = new query($db, "select passwd from user where login= :login", [":login" => $login]);
  * ...
  * </pre>
  * 
  * <h3>3. connection a database using its dns:</h3>
  * <pre>
  * require_once("lib/db.php");
  * require_once("lib/query.php");
  * $db = new db("mysql;host=loaclhost,dbname=my_local_db", "user_login", "user_passwd"); 
  * $q = new query($db, "select passwd from user where login= :login", [":login" => $login]);
  * ...
  * </pre>
  *
  * <h3>4. connection a database using a dbs entry</h3>
  * <p>
  * dbs is a database connection table. It stores dsn, logins and passwords of diffent databases connections.
  * db understands that if not a dsn-like strings is passed in its first arg $db_dsn, it must interpret 
  * the string as a dbs identifier and look for the correct parameters in the default database (tech.dbs).
  * </p>
  * <pre>
  * require_once("lib/db.php");
  * require_once("lib/query.php");
  * $db = new db("mysql;host=loaclhost,dbname=my_local_db", "user_login", "user_passwd"); 
  * $q = new query($db, "select passwd from user where login= :login", [":login" => $login]);
  * ...
  * 
  * </pre>
  * 
  * <h3>Notes</h3>
  * <p>
  * Since http is a disconnected protocol, the is no persistency of connections at this level. 
  * Persistency can be handled by pdo, if required. 
  * However, on an average http request in a well designed application, te overhead due to db connections
  * is/should be quite insignificant.
  * </p>
  *  
  */
class db {
	private $pdo;
	/**
	 *  @param $db_dsn may: 
	 *  - be unset or set to false, in which case the default database configured in conf/db.php will be used 
	 *  - contain a dbs, (dbs=db_connexion_id or db_connexion_id referenced in column dbs of the tech.dbs table of the default database)
     *    "dbs=default" or default stands for default database configured in conf/db.php
     *  - contain a proper pdo dsn  matched by '^([^:]*):(host=[^;]*);(port=([0-9]*);)?(dbname=(.*))$'
	 *  
     * 	<pre>
	 *  @param $db_user     if d$b_dsn is a proper dsn, must be set to database user login
	 *  @param $db_user     if $db_dsn is a proper dsn, must be set to database user password
	 *  @param $dboption    may contain options passed to $options argument of PDO constructor (see https://www.php.net/manual/class.pdo.php) 
	 *  </pre>
	 */
	function __construct($db_dsn = false, $db_user = false, $db_pass = false, $db_options = [PDO::ERRMODE_WARNING => true]) {
		$this->status    = false;
		$this->db_drv    = false;
		$this->db_name   = false;

		if ($db_dsn === false || $db_dsn == "dbs=default" || $db_dsn == "default") {
			if (file_exists("conf/db.php")) {
				include("conf/db.php");
				if (!isset($db_dsn) || $db_dsn === false) {
					if (!isset($db_drv) || $db_drv === false) $db_drv = "mysql";
					if (isset($db_port) || $db_port !== false) $port="port=$db_port;"; else $port = "";
					$db_dsn = "$db_drv:host=$db_host;".$port."dbname=$db_name";
					if ($db_user === false && isset($db_usid)) $db_user = $db_usid;
				}
                                
				$this->db_drv  = $db_drv;
				$this->db_name = $db_name;
			} else return;
		} else {
			if (preg_match("/^([^:]*):(host=[^;]*);(port=([0-9]*);)?(dbname=(.*))$/", $db_dsn, $m)) {
				$this->db_drv  = $m[1];
				$this->db_name = $m[6];
			} else {
				if (preg_match("/^dbs=(.*)$/", $db_dsn, $m))
					$dbs = $m[1];
				else 
					$dbs = $db_dsn;

				$dd = new db();
				$q = $dd->query("select drv, host, port, name, \"user\", pass from tech.dbs where dbs = '$dbs'");
				if ($q === false) return;
				$o = $dd->obj();
				foreach (["drv", "host", "port", "name", "user", "pass"] as $k) {
					$this->{"db_".$k} = $o->$k;
				}	
				$db_user = $this->db_user;
				$db_pass = $this->db_pass;
				$db_dsn = "$this->db_drv:host=$this->db_host;".$this->db_port."dbname=$this->db_name";
			} 
		}

		try {
			$this->pdo = new PDO($db_dsn, $db_user, $db_pass, $db_options);
		} catch (PDOException $e) {
			_err("Cannot connect to db using $db_dsn: " . $e->getMessage());
			return;
		}	
		if (($i = $this->pdo->errorInfo()) !== false && $i[1] != null) {
			_err("$i[0]: $i[1] - $i[2]"); 
			$this->pdo = false;
		}
		$this->status = true;
	}

	/**
     * check connection
	 */
	function check_cnx() {
		if ($this->pdo === false) return false;
		return true;
	} 
	function prepare($sql) {
		if ($this->pdo === false) return false;
		return $this->pdo->prepare($sql);
		return true;
		
	}
	/**
	 * Return db database driver name
	 */
	function driver()  { return $this->db_drv;  }
	/**
	 * Return db database name
	 */
	function db_name() { return $this->db_name; }
	/**
	 * List accessible databases within the data server
	 */
	function databases()  {
		$sql = "";
		if ($this->db_drv == "mysql") 
			$sql = "select distinct table_schema as db from information_schema.tables";
		else if ($this->db_drv == "pgsql") {
			$sql = "select distinct table_catalog as db from information_schema.tables";
		}
		$this->query($sql);
		$t = [];
		while ($r = $this->obj()) {
			array_push($t, $r->db);
		}
		return $t;
	}
	/**
	 * List accessible schemas in the current database:
	 */
	function schemas()  {
		$sql = "select distinct table_schema from information_schema.tables where table_catalog = '$this->db_name'";
		$this->query($sql);
		$t = [];
		while ($r = $this->obj()) {
			array_push($t, $r->table_schema);
		}
		return $t;
	}
	/**
	 * List accessible tables in the current database and given schema (if any given)
	 */
	function tables($schema = "")  {
		$sql = "";
		if ($this->db_drv == "mysql") 
			$sql = "select table_name from information_schema.tables where table_schema = '$this->db_name'";
		else if ($this->db_drv == "pgsql") {
			$sql = "select table_name from information_schema.tables where table_catalog = '$this->db_name'";
			if ($schema != "") {
				$sql .= "and table_schema = '$schema'";
			}
		}
		$this->query($sql);
		$t = [];
		while ($r = $this->obj()) {
			array_push($t, $r->table_name);
		}
		return $t;
	}

	/**
	 * List table columns 
	 */
	function table_columns($table) {
		$cols = [];

		if ($this->db_drv == "mysql") {
			$sql = "select column_name, is_nullable, data_type, column_default, character_maximum_length from information_schema.COLUMNS where TABLE_SCHEMA = '$this->db_name' and TABLE_NAME = '$table'";
		} else if ($this->db_drv == "pgsql") {
			if (preg_match("/^([^.]*).(.*)$/", $table, $m)) {
				$schema = $m[1];
				$table  = $m[2];
			} else {
				$schema = "public_schema";
			}
			$sql = "select COLUMN_NAME, IS_NULLABLE, udt_name as DATA_TYPE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH from information_schema.COLUMNS where TABLE_CATALOG = '$this->db_name' and TABLE_SCHEMA = '$schema' and TABLE_NAME = '$table'";
		} else {
			_err("bad db driver $this->db_drv");
			return [];
		}
		
		$this->query($sql);
		while ($r = $this->data()) {
			$c = (object) [];
			foreach ($r as $k => $v) { 
				if ($k == "column_default" && preg_match("/^([^:]*)::(.*)$/", $v, $m)) $v = $m[2];
				$c->$k = $v; 
			}
			$cols[$c->column_name] = $c;
		}
		return $cols;
	}

	private function _table_keys_mysql($table) { 
		$sql = "select column_name as name, column_key as `key` from information_schema.COLUMNS where TABLE_SCHEMA = '$this->db_name' and TABLE_NAME = '$table' and COLUMN_KEY != ''";
		$kp = $km = [];

		$this->query($sql);
		while ($o = $this->obj()) {
			if ($o->key == 'PRI') array_push($kp, $o->name);
			else                  array_push($km, $o->name);
		}
		if ($kp != []) return $kp;
		return $km;
		
	}
	private function _table_keys_pgsql($table) { 
		if (preg_match("/^([^.]*).(.*)$/", $table, $m)) {
			$schema = $m[1];
			$table  = $m[2];
		} else {
			$schema = "public_schema";
		}
		$sql = "select column_name from  information_schema.table_constraints tc, information_schema.key_column_usage kc where "
				. "kc.table_name = tc.table_name and kc.table_schema = tc.table_schema and kc.constraint_name = tc.constraint_name and kc.table_catalog = tc.table_catalog "
				. "and tc.constraint_type = 'PRIMARY KEY' and tc.table_catalog = '$this->db_name' and tc.table_schema = '$schema' and tc.table_name = '$table'";

		$this->query($sql);

		$kl = [];
		while ($o = $this->obj()) array_push($kl, $o->column_name);
		return $kl;
	}

	/**
	 * List table keys
	 */
	function table_keys($table) {
		$fct = "_table_keys_".$this->db_drv;
		if (method_exists($this, $fct)) return $this->$fct($table);	
		return [];
	}
	
	/**
	 * List table foreign keys
	 */
	function table_fkeys($table) {
		$fct = "_table_fkeys_".$this->db_drv;
		if (method_exists($this, $fct)) return $this->$fct($table);	
		return [];
	}
        
	function src_fk($table) {
		switch($this->db_drv) {
		case "mysql":
			break;
		case "pgsql":
			if (preg_match("/^([^.]*).(.*)$/", $table, $m)) {
				$schema = $m[1];
				$table  = $m[2];
			} else {
				$schema = "public_schema";
			}
			$sql = 
"select 
	distinct fk_tco.table_schema || '.' || fk_tco.table_name as fk_table_name
	,key_col.column_name
	,pk_tco.table_schema         || '.' || pk_tco.table_name as primary_table_name
	,pk_tco.constraint_name
 from 
	information_schema.referential_constraints rco
 	INNER join information_schema.key_column_usage key_col on key_col.constraint_name    = rco.constraint_name
	join information_schema.table_constraints fk_tco       on rco.constraint_name        = fk_tco.constraint_name and rco.constraint_schema        = fk_tco.table_schema
	join information_schema.table_constraints pk_tco       on rco.unique_constraint_name = pk_tco.constraint_name and rco.unique_constraint_schema = pk_tco.table_schema
 where 
	fk_tco.table_name = '$table' 
    and fk_tco.table_catalog = '$this->db_name'
    and fk_tco.table_schema = '$schema'
order by 
	fk_table_name";
			$this->query($sql);
			break;
		}
	}

######################################################################################
# Undocumented stuff for internal or rare usecases:
#
	function query($sql) {
		if ($this->pdo === false) return false;
		try {
			$this->stmt   = $this->pdo->prepare($sql);
		} catch (PDOException $e) {
			$this->stmt == false;
			$i =  $this->stmt->errorInfo();
			_err($i);
			return false;
		}
		try {
			$this->status = $this->stmt->execute();
		} catch (PDOException $e) {
			$i =  $this->stmt->errorInfo();
			if (is_array($i)) _err(print_r($i, TRUE) . "\nstatement = $sql");
			else _err($i);
			return false;
		}
		return true;
	}
	function error() {
		if ($this->pdo === false) {
			return "no db";
		}
		if ($this->status === false) {
			$i =  $this->stmt->errorInfo();
			return "$i[0]: $i[1] - $i[2]"; 
		}
		return false;
	}
	function nrows() {
		if ($this->stmt === false) return false;
		return $this->stmt->rowCount();
	}
	function data() {
		if ($this->stmt === false) return false;
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	function obj() {
		if ($this->stmt === false) return false;
		return $this->stmt->fetchObject();
	}
	function all() {
		if ($this->stmt === false) return false;
		$a = [];
		while ($o = $this->obj()) {
			array_push($a, $o);
		}
		return $a; 
	}
#
######################################################################################
/*
 *  Obsolete features:

	function entity($name, $tmpl = []) { 
		$sql = "select * from $name";

		$tc = $this->table_columns($name);
		if ($tc == []) return false;

		$where = [] ;
		if ($tmpl != []) {
			foreach ($tmpl as $k => $v) {
				array_push($where, "$k = $v");
			}	
		}
		if ($where != []) $wc = " where" . implode(" and ", $where);
		
		$this->query($sql . " $wc");
		return $this->obj();
	}

	function entity_list($name, $tmpl = []) { 
		$sql = "select * from $name";

		$tc = $this->table_columns($name);
		if ($tc == []) return false;

		$where = [] ;
		if ($tmpl != []) {
			foreach ($tmpl as $k => $v) {
				array_push($where, "$k = $v");
			}	
		}
		if ($where != []) $wc = " where" . implode(" and ", $where);
		
		$this->query($sql . " $wc");
		return $this->all();
	}
****/
}
?>
