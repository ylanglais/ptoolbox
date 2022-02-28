<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");

#class dscsv($filename, sep=';', del='"') {
#}
/*
class prov {
	function __construct($datasource, $filter) {
	}
	function filter() {
	}
	function nrows() {
	}
	function query() {
	} 
	function obj() {
	}
	function data() {
	}
}
*/

class prov { 
	function __construct($d, $table = null, $filter = null) {
		if (is_object($d)) {
			$this->dsrc   = $d->dsrc;
			$this->table  = $d->table;
			$this->filter = $d->filter;
			$this->cols   = $d->cols;
			$this->fields = $d->fields;
			$this->keys   = $d->keys; 
			$this->count  = $d->count;
		} else if (is_array($d)) {
			$this->dsrc   = $d["dsrc"];
			$this->table  = $d["table"];
			$this->filter = $d["filter"];
			$this->cols   = $d["cols"];
			$this->fields = $d["fields"];
			$this->keys   = $d["keys"]; 
			$this->count  = $d["count"];
		} else {
			$this->dsrc   = $d;
			$this->table  = $table;
			$this->filter = $filter;
			$this->cols   = [];
			$this->fields = [];
			$this->keys   = [];
			$this->count  = false;
		}
		$this->db  = new db($this->dsrc);
		if ($this->cols   == []) {
			$this->cols   = $this->db->table_columns($table);
		} 
		if ($this->keys   == []) {
			$this->keys   = $this->db->table_keys($table);
		}
		if ($this->fields == []) {
			foreach ($this->cols as $f => $d) {
				array_push($this->fields, $f);
			}
		}
	}

	function fields() {
		return $this->fields;
	}

	function keys() {
		return $this->keys;
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
					switch($this->cols[$k]->type) {
					case "boolean":
					case "integer":
					case "smallint":
					case "decimal":
					case "numeric":
					case "real":
					case "double":
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
		$sql = "select count(*) from $this->table " . $this->_whereclause();
		$q = new query($this->db, $sql);
		if ($q->nrows() < 1) return false;
		$this->count = $q->obj()->count;
		return $this->count;
	}

	function query($start = 0, $limit = 25, $sortby = false, $order = false) {
		$q = "select ";
		if ($this->filter != null && is_array($this->filer->fields) && $this->filer->fields != []) {
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
		$q .= " offset $start limit $limit"; 
		#dbg("query= $q");
		$q = new query($this->db, $q);
		return $q->all();	
	}

	function data() {
		$d = (object) [];
		$d->dsrc   = $this->dsrc;
		$d->table  = $this->table;
		$d->filter = $this->filter;
		$d->cols   = $this->cols;
		$d->fields = $this->fields;
		$d->keys   = $this->keys; 
		$d->count  = $this->count; 
		return json_encode($d);
	} 

	function view() {
		return null;
	}
}


?>
