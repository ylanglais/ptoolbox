<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/store.php");

class prov_db { 
	function __construct($d, $table = null, $filter = null) {
		$loaded = false;
		$flds = [ "id", "dsrc", "table", "filter", "cols", "fields", "keys", "count", "type" ];

		if (is_string($d) && substr($d, 0, 10) == "__prov_db_") {
			$d = substr($d, 2);
			if (($o = store::get($d)) !== false) { 
				if (is_string($o)) { 
					#dbg($o);  
					$o = json_decode($o);
				}
				foreach ($o as $k => $v) {
					$this->$k = $v;
				}
				#dbg("$this->id restored");
				$loaded = true;
				$this->db  = new db($this->dsrc);
			} else {
				err("cannot restore $d");
				print("<h2>Bad data</h2></div>");
				exit();
			}
		} else if (is_string($d)) {	
			if (!preg_match("/^([^.]*)\.(.*)$/", $d, $m)) {
				print("<h2>Bad datalink</h2></div>");
				exit();
			}
			$base  = $m[1];
			$table = $m[2];

			$this->type   = "db";
			$this->dsrc   = $base;
			$this->id     = "prov_db_".$d."_".$table;

			$this->table  = $table;
			$this->filter = $filter;
			$this->cols   = [];
			$this->fields = [];
			$this->keys   = [];
			$this->count  = false;
			$this->filter = $filter;

			$this->db  = new db($this->dsrc);

			if ($this->cols   == []) $this->cols   = $this->db->table_columns($table);
			if ($this->keys   == []) $this->keys   = $this->db->table_keys($table);
			if ($this->fields == []) foreach ($this->cols as $f => $d) array_push($this->fields, $f);

			$o = (object) [];
			foreach ($flds as $k) $o->$k = $this->$k;
			store::put($this->id, json_encode($o));
		} else if (is_object($d)) {
			if (property_exists($d, "prov")) 
				foreach ($d->prov as $k => $v) $this->$k = $v;
			else foreach ($d as $k => $v) $this->$k = $v;
		} else {
			err("\$d is " . gettype($d));
			
		}
	}

	function name() {
		return $this->table;
	}

	function fields() {
		return $this->fields;
	}

	function quote($f, $v) {
		if (property_exists($this->cols, $f)) {
			switch($this->cols->$f->data_type) {
				case "int":
				case "int2":
				case "int4":
				case "integer":
				case "boolean":
				case "smallint":
				case "bigint":
				case "decimal":
				case "numeric":
				case "real":
				case "double":
				case "double precision":
				case "smallserial":
				case "serial":
				case "bigserial":
					return $v;
					break;
				case "date":
				case "time":
				case "datetime":
					return "'$v'";
					break;
			}
			if ($v == null  || $v == "null") {
				#dbg("v is null");
				return "null";
			}
		}
		return "'" . esc($v) . "'";
	}

	function defval($f) {
		if (array_key_exists($f, $this->cols)) {
			return $this->cols[$f]["column_default"];
		}
		return "";
	}
	function nullable($f) {
		if (array_key_exists($f, $this->cols)) {
			return $this->cols[$f]["is_nullable"];
		}
		return false;	
	}
	function iskey($f) {
		if (array_key_exists($f, $this->keys)) return true;
		return false;
	}

	function keys() {
		return $this->keys;
	}

	function key_to_id($key) {
		return urlencode(json_encode($o));
	}
	function id_to_key($id) {
		return json_decode(urldecode($id));
	}

	function _whereclause() {
		$q = "";
		if ($this->filter != null && is_array($this->filter->conditions) && $this->filter->conditions != []) {
			# condition is based on a key value pair with %:
			$q .= " where ";
			$i = 0;
			foreach ($this->filter->conditions as $k => $v) {
				if ($i > 0) $q .= " and";
				$i++;
				if (array_key_exists($k, 	$this->cols)) {
					switch($this->cols[$k]["data_type"]) {
					case "int":
					case "int2":
					case "int4":
					case "integer":
					case "boolean":
					case "smallint":
					case "bigint":
					case "decimal":
					case "numeric":
					case "real":
					case "double":
					case "double precision":
					case "smallserial":
					case "serial":
					case "bigserial":
						$q .= "$k = $v";
						break;
					case "date":
					case "time":
					case "datetime":
						$q .= "$k = '$v'";
						break;
					default:
						$q .= "$k like '" . esc($v) . "'";
					}
				} 
			}
		}
		return $q;
	}

	function count() {
		if ($this->count !== false) return $this->count;
		$sql = "select count(*) as count from $this->table " . $this->_whereclause();
		$q = new query($this->db, $sql);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}

	function query($start = 0, $limit = 25, $sortby = false, $order = false) {
		$q = "select ";
		if ($this->filter != null && is_array($this->filter->fields) && $this->filter->fields != []) {
			$this->fields = $this->filter->filelds;
			$q .= implode(", ", $this->fields); 
		} else {
			$q .= "*"; 
		}

		#$i = 0;
		foreach ($this->keys as $k) {
			#if ($i > 0) $q .= ", ";	
			$q .= ", $k as _hidden_$k"; 
			#$i++;
		}

		$q .= " from $this->table";	

		$q .= $this->_whereclause();

		if ($sortby !== false) {
			$q .= " order by $sortby";
			if ($order !== 'up')
				$q .= " desc";
		}
		$q .= " limit $limit offset $start"; 
		#dbg("query= $q");
		$q = new query($this->db, $q);
		return $q->all();	
	}

	function get($req) {
		$w = [];
		#dbg(json_encode($req));

		foreach ($req as $k => $v) {
			array_push($w, "$k = ". $this->quote($k, $v));
		}
		$where = " where " . implode(" and ", $w);
		#dbg("select * from $this->table $where");
		$q = new query($this->db, "select * from $this->table $where");
		
		return $q->obj();
	}
	function put($req) {
		#dbg("req = " . json_encode($req));
		$cols = [];
		$vals = [];
		//foreach ($req as $k => $v) {
			//array_push($vals, "$k = ". $this->quote($k, $v));
		//}
		foreach ($this->fields as $f) {
			if (property_exists($req, $f)) {
				array_push($cols, $f);
				array_push($vals,  $this->quote($f, $req->$f));
			} else if (array_key_exists("column_default", $this->cols[$f])) {
				array_push($cols, $f);
				array_push($vals, "$k = ". $this->quote($f, $this->cols[$f]["column_default"]));
			} else if ( $this->cols[$f] === false) {
				return false;
			}
		}
		$sql = "insert into $this->table (".  implode(",", $cols) . ") values (". implode(",", $vals) .")";
		#dbg("sql=<<$sql>>");
		
		$q = new query($this->db, $sql);
		if ($q->nrows() != 1) {
			return false;
		}
		return true;
	}
	function update($req) {
		$upd = [];
		$key = [];
		$whr = [];
	
		foreach ($this->keys as $k) {
			if (!property_exists($req, $k)) {
				err("missing key field $k");
				return false;
			}
			$key[$k] = $req->$k;
			array_push($whr, "$k = " .$this->quote($k, $req->$k));
		}
		
		if (($o = $this->get($key)) === false) {
			return $this->put($req);
		}
		
		foreach ($this->fields as $f) {
			if (!property_exists($req, $f)) continue;
			if ( array_key_exists($f, $key)) continue;
			if ($o->$f == $req->$f) 	     continue;
			array_push($upd, "$f = " . $this->quote($f, $req->$f));
		}
		
		$sql = "update $this->table set " . implode(",", $upd) . " where " . implode (" and ", $whr);

		#dbg("sql=<<$sql>>");
		
		$q = new query($this->db, $sql);
		if ($q->nrows() != 1) {
			return false;
		}
		return true;
	}

	function del($req) {
		$w = [];
		foreach ($req as $k => $v) {
			array_push($w, "$k = ". $this->quote($k, $v));
		}
		$where = " where " . implode(" and ", $w);
		$sql = "delete from $this->table $where";
		#dbg("sql=<<$sql>>");
		$q = new query($this->db, $sql);
		
		return $q->obj();
	}
	function data() {
		return '"'. "__" . $this->id . '"';
	} 

	function view() {
		return null;
	}
}
?>
