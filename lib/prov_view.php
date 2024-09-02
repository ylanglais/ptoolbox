<?php
require_once("lib/prov.php");
require_once("lib/db.php");
require_once("lib/query.php");

class prov_view {
	function __construct($view = null, $filter = null) {
		$this->id     = $view;
		$this->init   = false;
		$this->type   = "view";
		$this->name   = "";
		$this->tables = (object)[];
		$this->fields = [];
		$this->cols   = (object)[];
		$this->frags  = (object)[];
		$this->qry    = (object)[];
		$this->slist  = [];
		$this->joins  = [];
		$this->keys   = [];
		$this->perm   = 'NONE';
		$this->view   = false;
		$this->filter = $filter;
		$restored     = false;
		
		$view = unb64($view);
		if (is_string($view) && substr($view, 0, 12) == "__prov_view_") { 
			if (($o = store::get($view)) !== false) { 
				if (is_array($o) || is_object($o)) {
					foreach ($o as $k => $v) {
						$this->$k = $v;
					}
					##dbg("$view restored");
					$restored = true;
				} else {
					err("\$o is ".gettype ($o). " and contains " . json_encode($o));
					exit();
				}
			} else {
				err("cannot restore $d");
				print("<h2>Bad data</h2></div>");
				exit();
			}
		} else {
			$perm = get_perm("view", $view);
			if ($perm != 'RONLY' && $perm != 'ALL') {
				$s =  "Attempted access to view $view without due permission";
				audit_log("SECURITY", $s);
				err("SECURITY: " . get_user(). " $s");
			}
			$this->perm = $perm;

			$q = new query("select * from param.entity where name = '$view' and etype = 'view'");

			if (($o = $q->obj()) === false) {
				err("no view named '$view'");
				return;	
			}
			$this->name = $view;
			$this->view = (object)[];
			foreach ($o as $k => $v) $this->view->$k = $v;

			$this->view->dsrc = $this->_ckds($this->view->dsrc);
			$this->id     = "__prov_view__" . $this->name;

			$this->view->tid = $this->_tid($this->view->dsrc, $this->view->tname);
			$this->_add_table($this->view->dsrc, $this->view->tname);

			$this->refs     = [];
			$q = new query("select * from param.fragment where entity = '$this->name' order by forder");
			#
			# view:
			# type: fragment type 
			#	column: 	source table column, 
			#	reference:	ref value in an indexed look up table (1:1 relation)
			#	vallist:	Values from a 1:n) relation from source table to other table
			#	
			# name:
			#  
				
			/***
			select 
				ref.title.value as "Intitulé", 
				public.person.first_name as "Prénom", 
				public.person.last_name  as "Nom", 
				ref.gender.value as "Genre" 
			from 
				public.person 
				left join ref.title  on ref.title.id  = public.person.title 
				left join ref.gender on ref.gender.id = public.person.gender;
			***/
			$db = new db();
			$keys = (array) $db->table_keys($this->view->tname);

			while ($o = $q->obj()) {
				$this->frags->{$o->name} = (object)[];
				foreach ($o as $k => $v) { 
					#if ($k != 'name' && $v != null) 
					$this->frags->{$o->name}->{$k} = $v;
				}
				if ($this->frags->{$o->name}->type == "column") {
					$this->cols->{$o->name} = $this->tables->{$this->view->tname}->cols->{$o->sc};
					array_push($this->fields, $o->name);
					array_push($this->slist, $this->view->tname . ".$o->sc as \"$o->name\"");
					if (in_array($o->sc, $keys)) array_push($this->keys, $o->name);

				} else if ($this->frags->{$o->name}->type == "reference") {
					$this->cols->{$o->name} = $this->tables->{$o->ft}->cols->{$o->fdc};
					#
					# Set nullability to origin table: 
					$this->cols->{$o->name}->is_nullable = $this->tables->{$this->view->tname}->cols->{$o->sc}->is_nullable;

					$this->cols->{$o->name}->ftable = $o->ft;
					$this->cols->{$o->name}->fcol   = $o->fdc;
					if (in_array($o->sc, $keys)) array_push($this->keys, $o->name);
					array_push($this->fields, $o->name);
					array_push($this->slist, "$o->ft.$o->fdc as \"$o->name\"");
					array_push($this->joins, "left join $o->ft on " . $this->view->tname . ".$o->sc = $o->ft.$o->fjc");
				} else if ($this->frags->{$o->name}->type == "vallist") {
				} else if ($this->frags->{$o->name}->type == "values") {
				} else if ($this->frags->{$o->name}->type == "entity") {
				} else if ($this->frags->{$o->name}->type == "entitylist") {
				} else {
				}
			}
			store::put($this->id, $this);
		}
		$this->init = true;
	}
	private function _add_table($ds, $tn) {
		if (!is_object($this->tables)) $this->tables = (object)[];
		$dt        = (object)[] ;
		$ds        = $this->_ckds($ds);
		$dt->dsrc  = $ds;
		$dt->table = $tn;
		$dt->cols  = $this->_get_table_cols($ds, $tn);
		$this->tables->{$tn} = $dt;
	}
	private function _ckds($ds) {
		if ($ds == null || $ds == "null" || $ds == "") return "default";
		return $ds;
	} 
	private function _tid($ds, $tname) {
		$ds = $this->_ckds($ds);
		return $ds . "__". $tname;
	}
	private function _get_table_cols($ds, $tname) {
		if ($ds == null || $ds == "null") $ds = "default";
		$d = new db($ds);
		$tc = (object) $d->table_columns($tname);
		if ($tc == null || $tc == []) {
			warn("cannot get table $tname");
			return null;
		}
		return $tc;
	}
	function filter($filter = null) {
		if (is_null($filter)) return $this->filter;
		return $this->filter = $filter;	
	}
	#

	function name2sc($name) {
		return $this->cols->{$name}->sc;
	}

	function col_data($name) {
		if ($this->init === false || !property_exists($this->frags, $name)) return false;
		return $this->frags->{$name};
	}
	function name() {
		return $this->name;
	}	
	function fields() {
		return $this->fields;
	}
	function perm() {
		if ($this->init === false) return false;
		return $this->perm;
	}
	function val2cval($name, $val) {
		if ($this->init === false) return $val;
		if (($c = $this->col_data($name)) == false) return $val;
		if ($c->type == "column") return $val;
		if ($c->type == "reference") {
			if ($val == "null") { 
				$s = "select " . $c->fjc . " from " . $c->ft . " where " . $c->fdc . " is null ";
			} else if (strstr($val, "%")) { 
				$s = "select " . $c->fjc . " from " . $c->ft . " where lower(cast(" . $c->fdc . " as char(1000))) like lower('$val')";
			} else {
				$s = "select " . $c->fjc . " from " . $c->ft . " where " . $c->fdc . " = " . $this->quote($c->ft, $c->fdc, $val);
			}
			$q = new query($s);
			if ($q->nrows() < 1) return false;
			if ($q->nrows() == 1) return $q->obj()->{$c->fjc};
			return $q->all();
		}
		return $val;
	}
	function fquote($fld) {
		$rsv_word = ['user', 'right'];
		if (in_array($fld, $rsv_word )) {
			return '"' . $fld . '"';
		} 
		return $fld;
	}
	function quote($table, $f, $v) {
		if ($this->init === false) return $v;
		if (!property_exists($this->tables, $table)) {
			err("no table $table");
			return $v;
		}
		if (!property_exists($this->tables->{$table}->cols, $f)) {
			err("no column $f found in $table");
			return $v;
		}
		$c = $this->tables->{$table}->cols->{$f};

		switch($c->data_type) {
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
			if (!is_numeric($v) || $v === '' || $v === null) return "null";
			return $v;
			break;
		case "date":
		case "time":
		case "datetime":
			if ($v == "") return "null";
			return "'$v'";
			break;
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
	function datatype($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
			return $this->cols->{$f}->data_type;
		}
		return false;
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
	function type() {
		return $this->type;
	}
	function count() {
		if ($this->init  === false) return false;
		$s = "select count(*) from ". $this->view->tname . " " . implode(' ', $this->joins) ;
		$q = new query($s);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}
	function get($req, $limit = 0, $start = 0, $sortby = false, $order = false) {
		if ($this->init === false) return false;
		$w = [];
		foreach ($req as $k => $v) {
			if ($this->frags->{$k}->type == "column") {
				if ($v == null) array_push($w, "$k is null"); 
				else            array_push($w, $this->frags->{$k}->sc. " = ". $this->quote($this->view->tname, $this->frags->{$k}->sc, $v));
			} else if ($this->frags->{$k}->type == "reference") {
				#
				# if column is not nullable => add table and hard join:
				$t = $this->frags->{$k}->ft;
				$f = $this->frags->{$k}->fdc;
				if ($v == null) array_push($w, "$t.$f is null"); 
				else            array_push($w, "$t.$f = ". $this->quote($t, $f, $v));
			}
		}
		$where = " where " . implode(" and ", $w);
		$s = "select " . implode(', ', $this->slist) . " from ". $this->view->tname . " " . implode(' ', $this->joins) . " $where";
		$q = new query($s);		
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

		$flds = [];
		$vals = [];

		foreach ($this->frags as $k => $f) {
			array_push($flds, $f->sc);
			if ($f->type == "column") {
				array_push($vals, $this->quote($this->view->tname, $f->sc, $dat->{$k}));
			} else if ($f->type == "reference") {
				$v = $this->val2cval($k, $dat->{$k});
				array_push($vals, $this->quote($this->view->tname, $f->sc, $v));
			} 
			
		}
		$s = "insert into " . $this->view->tname . " (" . implode($flds, ",") . ") values (" . implode($vals, ",") . ")";
		$q = new query($s);
		if ($q->nrows() != 1) {
			err("$s : " . $q->err());
			return  '{"status": false, "query": "'.$sql.'", "error": "'.$q->err().'"}';
		}
		return true;
	}
	function _where($keyvals) {
		$w = [];
		foreach ($keyvals as $k => $v) {
			if ($this->frags->{$k}->type == 'reference') {
				$v = $this->val2cval($k, $v);
			} 
			$f = $this->frags->{$k}->sc;
			$v = $this->quote($this->view->tname, $f, $v);
			$f = $this->fquote($k);
			if ($v == null || $v == 'null') {
				array_push($w, "$f is null"); 
			} else {
				array_push($w, "$f = $v");
			}
		}
		return implode(" and ", $w);
	}
	function _exists($keyvals) {
		if ($this->init === false) {
			err("provider not initialized");
			return false;
		}

		$s = "select * from " . $this->view->tname . " where " . $this->_where($keyvals);
		#dbg("-> $s");
		$q = new query($s);
		if ($q->nrows() < 1) return false;
		return true;
	}
	function update($data) {
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

		$ori = $data->ori;
		
		$set = [];
		$whe = [];
		#
		# loop on fragment fields: 
		foreach ($this->frags as $k => $fr) {
			if ($fr->type == "column") {
				$ov = $ori->{$k};
				$nv = $dat->{$k};
				$f  = $fr->sc;
			} else if ($fr->type == "reference") { 
				$ov = $this->val2cval($k, $ori->{$k});
				$nv = $this->val2cval($k, $dat->{$k});
				$f  = $fr->sc;
			} 
			#dbg("$k: $fr->type , $f => ov = $ov, nv = $nv");
			
			# 
			# if column is key => add it to where clause:
			if ($this->keys == [] || in_array($k, $this->keys)) {
				$ov = $this->quote($this->view->tname, $f, $ov);
				if (!is_int($ov) && ($ov === null || $ov == "null")) 
					array_push($whe, $f . " is null");
				else
					array_push($whe, $f . " = $ov");
			}	
			#
			# If new data != ori => add set member:
			if ($dat->{$k} != $ori->{$k}) {
				$nv = $this->quote($this->view->tname, $f, $nv);
				array_push($set, $fr->sc . " = $nv");
			}
		}
				
		$s = "update " . $this->view->tname . " set " . implode(", ", $set) . " where " . implode(" and ", $whe);
		#dbg(">> update: $s");
		$q = new query($s);

		if ($q->nrows() != 1) {
			err("$s : " . $q->err());
			return  '{"status": false, "query": "'.$s.'", "error": "'.$q->err().'"}';
		}
		return true;
	}
	function del($data) {
		if ($this->init === false) {
			err("provider not initialized");
			return '{"status": false; "error": "provider not initialized"}';
		}
		if ($this->perm == 'RONLY') {
			err("cannot insert readonly data");
			return '{"status": false; "error": "cannot insert readonly data"}';
		}
		$dat = $data->data;
		
		$set = [];
		$whe = [];
		#
		# loop on fragment fields: 
		foreach ($this->frags as $k => $fr) {
			if ($fr->type == "column") {
				$nv = $dat->{$k};
				$f  = $fr->sc;
			} else if ($fr->type == "reference") { 
				$nv = $this->val2cval($k, $dat->{$k});
				$f  = $fr->sc;
			} 
			# 
			# if column is key => add it to where clause:
			if ($this->keys == [] || in_array($k, $this->keys)) {
				$nv = $this->quote($this->view->tname, $f, $nv);
				if (!is_int($nv) && ($nv === null || $nv == "null")) 
					array_push($whe, $f . " is null");
				else
					array_push($whe, $f . " = $nv");
			}	
		}
		$s = "delete from " . $this->view->tname . " where " . implode(" and ", $whe);
#dbg($s);
		$q = new query($s);		

		return true;
	}
	function _quote_data($type, $val) {
		switch($type) {
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
			return $val;
			break;
		default:
			return "'" . esc($val) . "'";
		}
		return $val;
	}
	function _whereclause($filter = null) {
		if ($this->init === false) return false;
		$w = "";
		if ($filter != null && is_array($filter) && $filter != []) {
			# condition is based on a key value pair with %:
			$w .= " where ";
			$i = 0;
			foreach ($filter as $k => $v) {
				if ($i > 0) $w .= " and";
				$i++;
				$f = $this->frags->{$k}->sc; 
				if ($this->frags->{$k}->type == 'columns') {
					$vv = $v;
					$dt = $this->cols->{$k}->data_type;
				} else {
					$vv = $this->val2cval($k, $v);
					$dt = $this->tables->{$this->frags->{$k}->ft}->cols->{$this->frags->{$k}->fjc}->data_type;
					if (is_array($vv)) {
						$fin = $this->frags->{$k}->fjc;
						$w .= " $f in (";
						$vs = $vv;
						$vals = [];
						foreach($vs as $_v) {
							array_push($vals, $this->_quote_data($dt, $_v->{$this->frags->{$k}->fjc}));	
						}
						$w .= implode(",", $vals) . ")";	
						continue;	
					}
				}	
				switch($dt) {
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
					$w .= "$f = $vv";
					break;
				case "date":
				case "time":
				case "datetime":
					$w .= "$f = '$vv'";
					break;
				default:
					$w .= "$f like '" . esc($vv) . "'";
				}
			}
		}
		return $w;
	}
	function query($start = 0, $stop = 25, $sortby = false, $order = false, $filter = null) {
		$s = "select " . implode(', ', $this->slist) . " from ". $this->view->tname . " " . implode(' ', $this->joins);

		$s .= $this->_whereclause($filter);

		if ($sortby !== false) {
			$s .= " order by \"$sortby\"";
			if ($order !== 'up') $s .= " desc";
		}
		$s .= " offset $start limit $stop";
#dbg($s);
		$q = new query($s);
		return $q->all();	
	}
	function data() {
		if ($this->init === false) return false;
		return b64($this->id);
	} 

	function view() {
		if ($this->init === false) return false;
		return null;
	}

	function fdata($f, $str = false, $max = 20) {
		if ($this->init === false) return false;
		if (!in_array($f, $this->fields)) return false;

		$col   = false;
		$table = false;
		if ($this->frags->{$f}->type == "column") {
			$table = $this->view->tname;
			$col   = $this->frags->{$f}->sc;  
		} else if ($this->frags->{$f}->type == "reference") {
			$table = $this->frags->{$f}->ft;  
			$col   = $this->frags->{$f}->fdc;  
		} else {
			err("cols->{$f}->data_type = " . $this->cols->{$f}->data_type);
			return [];
		}
			
		$s = "select distinct $col from $table";
		if ($str !== false) {
			$s .= " where lower(cast($col as char(1000))) like lower('$str%')";
		} 
		$s .= " limit $max"; 
		#dbg(">v> $s");
		$q = new query($s);
		$d = [];
		while ($o = $q->obj()) array_push($d, $o->{$col});
		return $d;
	}
}

