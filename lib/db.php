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
     *  - contain a proper pdo dsn  matched by '^([^:]*):(host=[^;]*);(port=([0-9]*);)?(dbname=([^;]*))?'
	 *  
     * 	<pre>
	 *  @param $db_user     if d$b_dsn is a proper dsn, must be set to database user login
	 *  @param $db_user     if $db_dsn is a proper dsn, must be set to database user password
	 *  @param $db_options  may contain options passed to $options argument of PDO constructor (see https://www.php.net/manual/class.pdo.php) 
	 *  @param $db_opts		may contain dsn extra options 
	 *  </pre>
	 */
	function __construct($db_dsn = false, $db_user = null, $db_pass = null, $db_options = [PDO::ERRMODE_WARNING => true], $db_opts = null) {
		$this->status  = false;
		$this->db_drv  = false;
		$this->drv     = false;
		$this->_drv    = false;
		$this->db_name = false;
		$this->db_opts = null;

		$this->db_dsn  = $db_dsn;

		if ($db_dsn === false || $db_dsn == "dbs=default" || $db_dsn == "default") {
			if (file_exists("conf/db.php")) {
				include("conf/db.php");
				if (!isset($db_dsn) || $db_dsn === false) {
					if (!isset($db_drv) || $db_drv  === false) $db_drv = "mysql";
					if (isset($db_port) || $db_port !== false) $port="port=$db_port;"; else $port = "";
					$db_dsn = "$db_drv:host=$db_host;".$port."dbname=$db_name";
					if ($db_user === false && isset($db_usid)) $db_user = $db_usid;
				}
                                
				$this->drv     = $db_drv;
				$this->db_drv  = $db_drv;
				$this->db_dsn  = $db_dsn;
				$this->db_name = $db_name;
				$this->db_user = $db_user;
				$this->db_pass = $db_pass;
				if (isset($db_opts)) $this->db_opts = $db_opts;
			} else return;
		} else {
			if (preg_match("/^odbc:(.*)$/", $db_dsn, $m)) {
				$this->db_drv  = "odbc";
				$this->db_name = $m[1];
				$this->db_dsn  = $db_dsn;
			} else if (preg_match("/^([^:]*):(host=[^;]*);(port=([0-9]*);)?(dbname=([^;]*))(;(.*))?$/", $db_dsn, $m)) {
				$this->db_drv  = $m[1];
				$this->db_name = $m[6];
				$this->db_dsn  = $db_dsn;
			} else {
				if (preg_match("/^dbs=(.*)$/", $db_dsn, $m))
					$dbs = $m[1];
				else 
					$dbs = $db_dsn;

				$dd = new db();
				$q = $dd->query("select drv, host, port, name, \"user\", pass, opts from tech.dbs where dbs = '$dbs'");
				if ($q === false) return;
				$o = $dd->obj();
				foreach ($o as $k => $v) {
					$this->{"db_$k"} = $v;
				}
				if ($this->db_drv != "odbc") {
					$this->db_dsn  = "$this->db_drv:host=$this->db_host;port=".$this->db_port.";dbname=$this->db_name";
				} else { 
					$this->db_dsn  = "$this->db_drv:$this->db_name";
				}
			} 
		}

		#
		# Direct call to constructor should overload db / conf file data:
		if (isset($db_user) && $db_user !== null) $this->db_user = $db_user;
		if (isset($db_pass) && $db_pass !== null) $this->db_pass = $db_pass;
		if (isset($db_opts) && $db_opts !== null) $this->db_opts = $db_opts;

		if ($db_options !== null) $this->db_dvop = $db_options;

		$dsn = $this->db_dsn;
		if ($this->db_opts !== null) $dsn .= ";$this->db_opts";

		try {
			$this->pdo = new PDO($dsn, $this->db_user, $this->db_pass, $this->db_dvop);
		} catch (PDOException $e) {
			_err("Cannot connect to db using $this->db_dsn: " . $e->getMessage());
			$this->pdo = false;
			return;
		}	
		if (($i = $this->pdo->errorInfo()) !== false && $i[1] != null) {
			_err("$i[0]: $i[1] - $i[2]"); 
			$this->pdo = false;
		}

		if ($this->db_drv == "mysql") {
			$this->drv = "mysql";
			include_once("lib/dbdrv/mysql.php");
			$this->_drv = "_mysql";
		} else if ($this->db_drv == "pgsql") {
			$this->drv = "pgsql";
			include_once("lib/dbdrv/pgsql.php");
			$this->_drv = "_pgsql";
		} else if ($this->db_drv == "odbc") {
			#set autocommit:
			#odbc_setoption($this->conn, 1, 102, 1);

			$this->_drv = false;
			foreach (glob("lib/dbdrv/*.php") as $f) {
				include_once($f);
				$d  = basename($f, ".php");
				$dd = "_$d";
				$this->query($dd::version_qry(), false);
				$o  = $this->obj();
				if ($o !== false) {
					foreach ($dd::version_strings() as $str) {
						if (stripos($o->version, $str) !== false) {
							$this->_drv = $dd;
							$this->drv  = $d;
							break;
						}
					}
				}
				if ($this->_drv !== false) break;
			}
		} 

		if ($this->_drv === false)
			warn("Databse is not fully supported (no access to metadata)");
		#else  
		#	dbg("db_drv = $this->drv over pdo_$this->db_drv");

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
	function driver()      { return $this->db_drv;  }
	/**
	 * Return db database real driver name (usefull with odbc drivers):
	 */
	function realdriver()  { return $this->drv;  }
	/**
	 * Return db database name
	 */
	function db_name() {
		if ($this->_drv === false || ($qry = $this->_drv::dbname_qry()) === false) {
			warn("method not supported with driver $this->drv");
			return "";	
		}
		$this->query($qry);
		$o = $this->obj();
		return $o->dbname;	
	}
	/**
	 * List accessible databases within the data server
	 */
	function databases()  {
		$t = [];
		if ($this->_drv === false || ($qry = $this->_drv::dbname_qry()) === false) {
			warn("method not supported with driver $this->drv");
			return $t;
		}

		$this->query($qry);
		while ($r = $this->obj()) { array_push($t, $r->dbname); }
		return $t;
	}
	/**
	/**
	/**
	 * List accessible schemas in the current database:
	 */
	function schemas()  {
		$t = [];
		if ($this->_drv === false || ($qry = $this->_drv::schemas_qry()) === false) {
			warn("method not supported with driver $this->drv");
			return $t;
		}
		
		$this->query($qry);
		while ($r = $this->obj()) { array_push($t, $r->schemas); }
		return $t;
	}
	/**
	 * List accessible tables in the current database and given schema (if any given)
	 */
	function tables($schema = "")  {
		$t = [];
		if ($this->_drv === false || ($qry = $this->_drv::tables_qry($schema)) === false) {
			warn("method not supported with driver $this->drv");
			return $t;	
		}
		
		$this->query($qry);
		while ($r = $this->obj()) { array_push($t, $r->tables); }
		return $t;
	}

	/**
	 * List table columns 
	 */
	function table_columns($table) {
		$cols = [];
		if ($this->_drv === false || ($qry = $this->_drv::columns_qry($table)) === false) {
			warn("method not supported with driver $this->drv");
			return $cols;	
		}
		
		$this->query($qry);
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
	
	/**
	 * List table keys
	 */
	function table_keys($table) {
		$t = [];
		if ($this->_drv === false || ($qry = $this->_drv::keys_qry($table)) === false) {
			warn("method not supported with driver $this->drv");
			return $t;	
		}
		
		$this->query($qry);
		while ($o = $this->obj()) array_push($t, $o->column_name);
		return $t;
	}
	
	/**
	 * List table foreign keys
	 */
	function table_fkeys($table) {
		$t = [];
		if ($this->_drv === false || ($qry = $this->_drv::fkeys_qry($table)) === false) {
			warn("method not supported with driver $this->drv");
			return $t;	
		}
		
		$this->query($qry);
		while ($o = $this->obj()) array_push($t, $o);
		return $t;
	}
        
######################################################################################
# Undocumented stuff for internal or rare usecases:
#
	function query($sql, $silent = false) {
		if ($this->pdo === false || $this->pdo == null) return false;
		try {
			$this->stmt   = $this->pdo->prepare($sql);
		} catch (PDOException $e) {
			$this->stmt == false;
			if (!$silent) {
				$i =  $this->stmt->errorInfo();
				_err($i);
			}
			return false;
		}
		try {
			$this->status = $this->stmt->execute();
		} catch (PDOException $e) {
			if (!$silent) {
				$i =  $this->stmt->errorInfo();
				if (is_array($i)) _err(print_r($i, TRUE) . "\nstatement = $sql");
				else _err($i);
			}
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
