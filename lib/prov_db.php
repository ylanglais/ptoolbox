<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/store.php");
require_once("lib/session.php");
require_once("lib/audit.php");
require_once("lib/util.php");

class prov_db { 
	function __construct($d, $table = null, $filter = null) {
		$this->id = false;
		$this->init = false;
		$loaded = false;
		$flds   = [ "id", "dsrc", "table", "filter", "cols", "fields", "keys", "fkeys", "count", "type", "perm" ];

		$d = unb64($d);

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

			$perm = get_perm("table", "$base.$table");
			if ($perm != 'RONLY' && $perm != 'ALL' && $perm != 'SYSTEM') {
				$s =  "Attempted access to table $d without due permission";
				audit_log("SECURITY", $s);
				err("SECURITY: ". get_user(). " $s");
				return;
			}

			$this->type   = "db";
			$this->dsrc   = $base;
			$this->id     = "prov_db_".$d."_".$table;

			$this->fdata_maxrow = 20; 

			$this->table  = $table;
			$this->cols   = (object)[];
			$this->fields = [];
			$this->keys   = [];
			$this->fkeys  = [];
			$this->count  = false;
			$this->filter = $filter;
			$this->perm   = $perm;

			$this->db  = new db($this->dsrc);

			if ($this->cols   == (object)[]) $this->cols   = (object) $this->db->table_columns($table);
			if ($this->keys   == []) $this->keys   		   = (array) $this->db->table_keys($table);
			if ($this->fkeys  == []) $this->fkeys          = (array) $this->db->table_fkeys($table);
			if ($this->fields == []) foreach ($this->cols as $f => $d) array_push($this->fields, $f);
			# Make sute to take all fields as key if no key defined (dangerous => no unicity):
			if ($this->keys == []) $this->keys = $this->fields;

			foreach ($this->fkeys as $fk) {
				#dbg($fk);
				$this->cols->{$fk->col}->ftable = $fk->ftable;
				$this->cols->{$fk->col}->fcol   = $fk->fcol;
			}

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
		$this->init = true;
	}
	function filter($filter = null) {
		if (is_null($filter)) return $this->filter;
		return $this->filter = $filter;	
	}
	#
	# quote fields if filed is a reserved word
	# Todo: Generalize in db driver
	function fquote($fld) {
		$rsv_word = ['user', 'right'];
		if (in_array($fld, $rsv_word )) {
			return '"' . $fld . '"';
		} 
		return $fld;
	}
	function name() {
		if ($this->init === false) return false;
		return $this->table;
	}
	function fields() {
		if ($this->init === false) return false;
		return $this->fields;
	}
	function perm() {
		if ($this->init === false) return false;
		return $this->perm;
	}
/**
	function is_text($f) {
		$tdt = [ "character", "varying", "varchar", "char", "bpchar", "text" ];

		if ($this->init === false) return false;
		if (!property_exists($this->cols, $f)) false;
		if (in_array($this->cols->$f->data_type, $tdt)) return true;
		return false;
	}
**/
	function quote($f, $v) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
			switch($this->cols->$f->data_type) {
				case "bool":
				case "boolean":
					if ($v === true) return 'true';
					return 'false';
					break;
				case "int":
				case "int2":
				case "int4":
				case "integer":
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
					if ($v === "" || $v === null) return "null";
					return $v;
					break;
				case "date":
				case "time":
				case "datetime":
				case "timestamp":
					if ($v == "") return "null";
					return "'$v'";
					break;
			}
		}
		if ($v === null  || $v === 'null' || $v == "") {
			return "null";
		}
		return "'" . esc($v) . "'";
	}

	function defval($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f) && property_exists($this->cols->{$f}, "column_default")) {
			return $this->cols->{$f}->column_default;
		}
		return "";
	}
	function nullable($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
			return $this->cols->{$f}->is_nullable;
		}
		return false;	
	}
	function iskey($f) {
		if ($this->init === false) return false;
		if (array_key_exists($f, $this->keys)) return true;
		return false;
	}
	function has_fk($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f) && property_exists($this->cols->{$f}, "ftable")) {
			return [ "ftable" => $this->cols->{$f}->ftable,"fcol" => $this->cols->{$f}->fcol];
		}
		return false;
	}
	function datatype($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
			return $this->cols->{$f}->data_type;
		}
		return false;
	}
	function keys() {
		if ($this->init === false) return false;
		return $this->keys;
	}

	function key_to_id($key) {
		return urlencode(json_encode($key));
	}
	function id_to_key($id) {
		return json_decode(urldecode($id));
	}

	function _whereclause($filter = null) {
		if ($this->init === false) return false;
		$w = "";
		if ($filter != null && is_object($filter)) $filter = (object) $filter;
		if (is_array($filter) && $filter != []) {
			$w = " where ";
			# condition is based on a key value pair with %:
			$i = 0;
			foreach ($filter as $k => $v) {
				if ($i > 0) $w .= " and ";
				$i++;
				if (property_exists($this->cols, $k)) {
					switch($this->cols->{$k}->data_type) {
					case "bool": 
					case "boolean": 
						$w .= "$k = $v";
						break;
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
						$w .= "$k = $v";
						break;
					case "date":
					case "time":
					case "datetime":
					case "timestamp":
						$w .= "$k = '$v'";
						break;
					default:
						$w .= "$k like '" . esc($v) . "'";
					}
				} 
			}
		}
			
		return $w;
	}

	function count($filter = null) {
		if ($this->init === false) return false;
		if ($this->count !== false) return $this->count;
		$sql = "select count(*) as count from $this->table " . $this->_whereclause($filter);
		$q = new query($this->db, $sql);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}

	function query($start = 0, $limit = 25, $sortby = false, $order = false, $filter = null) {
		if ($this->init === false) return false;
		$q = "select * ";

		#$i = 0;
		foreach ($this->keys as $k) {
			#if ($i > 0) $q .= ", ";	
			$q .= ", $k as _hidden_$k"; 
			#$i++;
		}

		$q .= " from $this->table";	

		$q .= $this->_whereclause($filter);

		if ($sortby !== false) {
			$q .= " order by $sortby";
			if ($order !== 'up')
				$q .= " desc";
		}
		if ($start > 0) $q .= " offset $start";	
		if ($limit > 0)  $q .= " limit $limit";
		#dbg("query= $q");
		$q = new query($this->db, $q);
		return $q->all();	
	}

	function get($req, $limit = 0, $start = 0, $sortby = false, $order = false) {
		if ($this->init === false) return false;
		$w = [];
		foreach ($req as $k => $v) {
			$t = $this->cols->{$k}->data_type;
			#dbg("$k => $v ($t vs ".gettype($v).")");
			if ($t == 'bool' || $t == 'boolean') {
				if ($v === true) array_push($w, "$k = true");
				else 			 array_push($w, "$k = false");
			} else if (gettype($v) == 'null' || $v === 'null' || $v === null) {
				array_push($w, "$k is null"); 
			} else {
	           array_push($w, "$k = ". $this->quote($k, $v));
			}
		}
		$where = " where " . implode(" and ", $w);
		$sql = "select * from $this->table $where";
		if ($sortby !== false) {
			$sql .= " order by $sortby";
			if ($order != false && (strtolower($order) == "asc" || strtolower($order) == "desc")) 
				$sql .= " $order";
		}
		if ($limit > 0)      $sql .= " limit $limit offset $start";
		#dbg($sql);
		$q = new query($this->db, $sql);
		return $q->all();
	}
	function put($data) {
		if ($this->init === false) {
			err("provider not initialized");
			return '{"status": false; "error": "provider not initialized"}';
		}
		if ($this->perm == 'RONLY') {
			err("cannot insert readonly data");
			return '{"status": false; "error": "cannot insert readonly data"}';
		}
		if (is_object($data) && property_exists($data, "data"))
			$dat = $data->data;
		else
			$dat = $data;

		if (is_array($dat)) $dat = (object) $dat;

		$tst = (object) [];
		foreach ($this->keys as $k) {
			if (property_exists($dat, $k)) { $tst->$k = $dat->$k; }
		}
		if (($ori = $this->get($tst)) != false) return $this->update((object) [ "data" => $dat, "ori" => $ori[0] ]); 

		$cols = [];
		$vals = [];

		foreach ($this->fields as $f) {
			$k = $this->fquote($f);
			if (property_exists($dat, $f)) {
				array_push($vals,  $this->quote($f, $dat->$f));
				array_push($cols, $k);
			} else if (property_exists($this->cols->$f, "column_default")) {
				array_push($vals, $this->quote($f, $this->cols->$f->column_default));
				array_push($cols, $k);
			} else if ($this->cols->$f === false) {
				err("$f is a required column");
				return '{"status": false; "error": "'. $f. '" is a required column"}';
			}
		}
		$sql = "insert into $this->table (".  implode(",", $cols) . ") values (". implode(",", $vals) .")";
		#dbg($sql);
		$q = new query($this->db, $sql);
		if ($q->nrows() != 1) {
			err("$sql : " . $q->err());
			return  '{"status": false, "query": "'.$sql.'", "error": "'.$q->err().'"}';
		}
		return true;
	}
	function update($data) {
		if ($this->init === false) {
			err("provider not initialized");
			return '{"status": false; "error": "provider not initialized"}';
		}
		if ($this->perm == 'RONLY') {
			err("cannot update readonly data");
			return '{"status": false; "error": "cannot insert readonly data"}';
		}
		if (!is_object($data) 
			|| !property_exists($data, "data")
			|| !property_exists($data, "ori")) {
			err("Incomplete data (".json_encode($data).")");
			return '{"status": false; "error": "incomplete data"}';
			return false;
		}
		#
		$ori = $data->ori;
		$dat = $data->data;

		$set = [];
		$whr = [];

		# create where clause:
		foreach ($this->fields as $k) {
			if (!property_exists($ori, $k)) {
				err("missing key field $k");
				return '{"status": false, "error": "missing key field '. $k .'"}';
			}
			
			$v = $this->quote($k, $ori->$k);
			if ($v == 'null' || $v === null) array_push($whr, "$k is null"); 
			else                             array_push($whr, "$k = $v");
		}

		$autoupdate = 0;
		foreach ($this->fields as $f) {
			if (property_exists($dat, $f) && (!property_exists($ori, $f) || $ori->$f != $dat->$f)) {
				if ($f == "mstamp") $autoupdate = 1;
				$k = $this->fquote($f); 
				$v = $dat->$f;
				array_push($set, "$k = " . $this->quote($f, $v));
			}
		}
		# Auto update of "updated" field:
		if (in_array("mstamp", $this->fields) && !$autoupdate) array_push($set, "mstamp = now()"); 

		if ($set != [] ) {
			$sql = "update $this->table set " . implode(",", $set) . " where " . implode (" and ", $whr);
			$q = new query($this->db, $sql);
			if ($q->nrows() != 1) {
				$e = $q->err();
				err("$sql : $e");
				return  '{"status": false, "query": "'.$sql.'", "error": "'.$e.'"}';
			}
		}
		return true;
	}
	function del($data) {
		if ($this->init === false) {
			err("provider not initialized");
			return '{"status": false; "error": "provider not initialized"}';
		}
		if ($this->perm == 'RONLY') {
			err("cannot delete readonly data");
			return '{"status": false; "error": "cannot insert readonly data"}';
		}
		if (is_object($data) || property_exists($data, "data")) { 
			$dat = $data->data;
		} else {
			$dat = $data;
		}
		$w = [];
		if ($this->fkeys != []) {	
			foreach ($this->fkeys as $f) {
				if  (!property_exists($dat, $f)) {
					err("Missing key $f, cannot delete (" . json_encode($dat) . ")");
					return false;
				}
				$k = $this->fquote($f);
				$v = $this->quote($f, $data[$f]);
				if ($v == null || $v == 'null') {
					array_push($w, "$k is null"); 
				} else {
		        	array_push($w, "$k = $v");
				}
			}
		} else {
			foreach ($dat as $f => $v) {
				$k = $this->fquote($f);
				$v = $this->quote($f, $v);
				if ($v == null || $v == 'null') {
					array_push($w, "$k is null"); 
				} else {
		        	array_push($w, "$k = $v");
				}
			}
		}
		$where = " where " . implode(" and ", $w);
		$sql = "delete from $this->table $where";
		#dbg($sql);
		$q = new query($this->db, $sql);
		
		if (($r = $q->obj()) === false) {
			err("$sql : " . $q->err());
			return  '{"status": false, "query": "'.$sql.'", "error": "'.$q->err().'"}';
		}
		return true;
	}
	function data() {
		if ($this->init === false) return false;
		return b64("__" . $this->id);
	} 
	function view() {
		if ($this->init === false) return false;
		return null;
	}
	function fdata($f, $str = false, $max = 20) {
		if ($this->init === false) return false;
		if (!in_array($f, $this->fields)) return false;
		$s = "select distinct $f from $this->table";
		if ($str !== false )  {
			$s .= " where lower(cast($f as char(1000))) like lower('$str%')";
		} 
		$s .= " order by 1 limit $max"; 
		#dbg(">d> $s");
		$q = new query($this->db, $s);
		$d = [];
		while ($o = $q->obj()) array_push($d, $o->{$f});
		return $d;
	}
}
?>
