
<?php
require_once("lib/db.php");
require_once("lib/dbg_tools.php");

/**
 * Simple db sql queries
 * This class can be used as is when dealing with the default database, or alongside with the db class (lib/db.php) for querying alternate database.
 * @see db. 
 * 
 * Example:
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
 */
class query {
	/**
 	 * @param Constructor can handle 3 arguments ($a1, $a2, $a3) :
	 * - string : interpreted as sql statement (normal or w/ preparation);
	 * - array  : interpreted as data array for preparation / exec;
	 * - object : interpreted as database connection.<br/>
	 * Order is meaningless, constructor picks arguments according to its type.
	 * 
	 * Examples:
     * <pre>
     *    require_once("lib/query.php");
     *    $q = new query("select passwd from user where login='$login'"); # not recommended if $login comes from user given argument...
     *    $q = new query("select passwd from user where login=:login", [":login" => $login ]);
     *    $q = new query([":login" => $login ], "select passwd from user where login=:login"); # equivalent to previous
     *
     *    require_once("lib/db.php");
     *    $db = new db("default");
     *    $q = new query($db, "select passwd from user where login='$login'");
     *    $q = new query("select passwd from user where login='$login'", $db);
     *    $q = new query($db, "select passwd from user where login=:login", [":login" => $login ]);
     *    $q = new query("select passwd from user where login=:login", [":login" => $login ], $db);
	 *    ...
     *
     * </pre>
	 */
	function __construct($a1, $a2 = false, $a3 = false) {
		$this->status = false;
		$sql  = false;
		$data = false;
		$odb  = false;

		foreach (["a1", "a2", "a3"] as $a) {
			if (is_string($$a)) {
				$sql = $$a;
			} else if (is_array($$a)) {
				$data = $$a;
			} else if (is_object($$a)) {
				$odb  = $$a;
			}
		}
		if ($sql === false || $sql == null || $sql == "") {
			_err("no statement");
			return;
		}

		if ($odb === false) $odb = new db();
		if ($odb === false || $odb->check_cnx() === false) {
			$this->stmt   = false;
			$this->status = false;
			return;
		}
		
		try {
			$this->stmt   = $odb->prepare($sql);
			if ($data !== false) 
				$this->status = $this->stmt->execute($data);
			else
				$this->status = $this->stmt->execute();
		} catch (PDOException $e) {
			if ($this->stmt   === false) _err("problem with statement: " . $this->error());
			if ($this->status === false) _err("problem with status:    " . $this->error());
		}
	}
	/**
     * Retrieve last error message (alias to error() method)
     */
	function err() {
		return $this->error();
	}
	/**
     * Retrieve last error message
     */
	function error() {
		if ($this->stmt === false) {
			return "no db connection";
		}
		if ($this->status === false) {
			$i =  $this->stmt->errorInfo();
			return "$i[0]: $i[1] - $i[2]"; 
		}
		return false;
	}
	/**
     * Get row count returned by the statement
     */
	function nrows() {
		if ($this->stmt === false) return false;
		return $this->stmt->rowCount();
	}
	/**
     * Return next data row as an associative array
     */
	function data() {
		if ($this->stmt === false) return false;
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	/**
     * Return next data row as an object:
     */
	function obj() {
		if ($this->stmt === false) return false;
		return $this->stmt->fetchObject();
	}
	/**
     * Return all rows as an array of objects:
     */
	function all() {
		if ($this->stmt === false) return false;
		$a = [];
		while ($o = $this->obj()) {
			array_push($a, $o);
		}
		return $a; 
	}

	/**
     * <pre>Return all rows as csv string
	 * @param $sep is the separator (default ;)
	 * @param $del is the delimitor (default none)</pre>
	 * @return csv string result
     */
	function csv($sep = ";", $del = "") {
		$all = "";
		while ($o = $this->obj()) {
			$r = [];
			foreach ($o as $k => $v) { array_push($r, "$v"); }
			$all .= implode($sep, $del.$r.$dem) . "\n";
		}
		return $all;
	}

	/**
     * Return all rows as json string
	 */
	function json() {
		$all = [];
		$all = $this->all();
		return json_encode($all);
	}

	/**
     * Return database now timestamp
	 */
	function now() {
		$qry = "select now() as 'now'";
		if (($req = $this->query($qry)) == null) {
			return null;
		}
		$dat = $this->data();
		return $dat['now'];
	}
}

?>
