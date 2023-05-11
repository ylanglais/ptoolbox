<?php
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/store.php");
require_once("lib/session.php");
require_once("lib/audit.php");

class prov_db { 
	function __construct($d, $table = null, $filter = null) {
		$this->init = false;
		$loaded = false;
		$flds   = [ "id", "dsrc", "table", "filter", "cols", "fields", "keys", "fkeys", "count", "type", "perm" ];

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

			$perm = get_perm("table", $d);
			if ($perm != 'RONLY' && $perm != 'ALL') {
				$s =  "Attempted access to table $d without due permission";
				audit_log("SECURITY", $s);
				err("SECURITY: ". get_user(). " $s");
				return;
			}

			$base  = $m[1];
			$table = $m[2];

			$this->type   = "db";
			$this->dsrc   = $base;
			$this->id     = "prov_db_".$d."_".$table;

			$this->table  = $table;
			$this->filter = $filter;
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
			#dbg($this->fkeys);
			# Make sute to take all fields as key if no key defined (dangerous => no unicity):
			if ($this->keys == []) $this->keys = $this->fields;

			foreach ($this->fkeys as $fk) {
				#dbg($fk);
				#$this->cols[$fk->col]->ftable = $fk->ftable;
				#$this->cols[$fk->col]->fcol   = $fk->fcol;
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
#
#file_put_contents(".dmp/$this->table.json", json_encode($this,  JSON_PRETTY_PRINT));
		$this->init = true;
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
		if (property_exists($this->cols, $f)) {
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

	function _whereclause() {
		if ($this->init === false) return false;
		$q = "";
		if ($this->filter != null && is_array($this->filter->conditions) && $this->filter->conditions != []) {
			# condition is based on a key value pair with %:
			$q .= " where ";
			$i = 0;
			foreach ($this->filter->conditions as $k => $v) {
				if ($i > 0) $q .= " and";
				$i++;
				if (property_exists($this->cols, $k)) {
					switch($this->cols->$k->data_type) {
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
		if ($this->init === false) return false;
		if ($this->count !== false) return $this->count;
		$sql = "select count(*) as count from $this->table " . $this->_whereclause();
		$q = new query($this->db, $sql);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}

	function query($start = 0, $limit = 25, $sortby = false, $order = false) {
		if ($this->init === false) return false;
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
		#dbg("select * from $this->table $where");
		$q = new query($this->db, "select * from $this->table $where");
		
		return $q->obj();
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
		if (!is_object($data) || !property_exists($data, "data")) {
			err("Incomplete data");
			return '{"status": false; "error": "incomplete data"}';
		}
		$dat = $data->data;

		$cols = [];
		$vals = [];

		foreach ($this->fields as $f) {
			if (property_exists($dat, $f)) {
				array_push($cols, $f);
				array_push($vals,  $this->quote($f, $dat->$f));
			} else if (property_exists($this->cols->$f, "column_default")) {
				array_push($cols, $f);
				array_push($vals, "$k = ". $this->quote($f, $this->cols->$f->column_default));
			} else if ($this->cols->$f === false) {
				err("$f is a required column");
				return '{"status": false; "error": "'. $f. '" is a required column"}';
			}
		}
		$sql = "insert into $this->table (".  implode(",", $cols) . ") values (". implode(",", $vals) .")";
		dbg($sql);
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
		# Do I *REALLY* nead req since I already have ori ???:
		$ori = $data->ori;
		$dat = $data->data;

		$set = [];
		$whr = [];

		# create where clause:
		foreach ($this->keys as $k) {
			if (!property_exists($ori, $k)) {
				err("missing key field $k");
				return '{"status": false, "error": "missing key field '. $k .'"}';
			}
			
			$v = $this->quote($k, $ori->$k);
			if ($v == 'null' || $v === null) array_push($whr, "$k is null"); 
			else                             array_push($whr, "$k = $v");
		}

		foreach ($this->fields as $f) {
			if ($ori->$f != $dat->$f) {
				array_push($set, "$f = " . $this->quote($f, $dat->$f));
			}
		}
		
		$sql = "update $this->table set " . implode(",", $set) . " where " . implode (" and ", $whr);

		$q = new query($this->db, $sql);
		if ($q->nrows() != 1) {
			$e = $q->err();
			err("$sql : $e");
			return  '{"status": false, "query": "'.$sql.'", "error": "'.$e.'"}';
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
		if (!is_object($data) || !property_exists($data, "data")) {
			err("Incomplete data");
			return '{"status": false; "error": "incomplete data"}';
		}
		$dat = $data->data;

		$w = [];
		foreach ($dat as $k => $v) {
			$v =$this->quote($k, $v);
			if ($v == null || $v == 'null') array_push($w, "$k is null"); 
			else            array_push($w, "$k = $v");
		}
		$where = " where " . implode(" and ", $w);
		$sql = "delete from $this->table $where";
		$q = new query($this->db, $sql);
		
		if (($r = $q->obj()) === false) {
			err("$sql : " . $q->err());
			return  '{"status": false, "query": "'.$sql.'", "error": "'.$q->err().'"}';
		}
		return true;
	}
	function data() {
		if ($this->init === false) return false;
		return '"'. "__" . $this->id . '"';
	} 
	function view() {
		if ($this->init === false) return false;
		return null;
	}
}
?>
