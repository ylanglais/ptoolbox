<?php
require_once("lib/prov.php");
require_once("lib/db.php");
require_once("lib/query.php");

class prov_view {
	function __construct($view = null, $filter = null) {
		$this->id    = $view;
		$this->init     = false;
		$this->type     = "view";
		$this->name     = "";
		$this->tables   = (object)[];
		$this->fields   = [];
		$this->cols     = (object)[];
		$this->fragment = (object)[];
		$this->qry      = (object)[];
		$this->slist    = [];
		$this->joins    = [];
		$this->keys     = [];
		$this->perm     = 'NONE';
		$this->view      = false;
		$restored       = false;

#dbg(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)));
#dbg($view);
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

			$this->view->tid = $this->_tid($tshis->view->dsrc, $this->view->tname);
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
				$this->fragment->{$o->name} = (object)[];
				foreach ($o as $k => $v) { 
					#if ($k != 'name' && $v != null) 
					$this->fragment->{$o->name}->{$k} = $v;
				}
				if ($this->fragment->{$o->name}->type == "column") {
/*
dbg(">> " . $this->view->tname);
dbg(">> " . $this->id);
dbg(">> " . $o->name);
dbg($this->tables);
*/
#dbg("name: $o->name, tid: $this->view->tid, data: " . json_encode ($this->tables->{$this->view->tid}));
					$this->cols->{$o->name} = $this->tables->{$this->view->tid}->cols[$o->cname];
					array_push($this->fields, $o->name);
					array_push($this->slist, $this->view->tname . ".$o->cname as \"$o->name\"");
					if (in_array($o->name, $keys)) array_push($this->keys, $o->name);
				} else if ($this->fragment->{$o->name}->type == "reference") {
					$tid = $this->_tid($o->fsrc, $o->ftname);
					$this->_add_table($o->fsrc, $o->ftname);
/*
dbg(json_encode($this->tables->{$tid}));
dbg("--->>> $o->flname");
dbg("---<<< $o->name");
dbg($this->cols);
*/
dbg("colname: $o->name, tid: $tid, flname: $o->flname");
					$this->cols->{$o->name} = $this->tables->{$tid}->cols[$o->flname];
					$this->cols->{$o->name}->ftable = $o->ftname;
					$this->cols->{$o->name}->fcol   = $o->flname;
					if (in_array($o->cname, $keys)) array_push($this->keys, $o->name);
					array_push($this->fields, $o->name);
					array_push($this->slist, "$o->ftname.$o->flname as \"$o->name\"");
					array_push($this->joins, "left join $o->ftname on " . $this->view->tname . ".$o->cname = $o->ftname.$o->finame");
				} else if ($this->fragment->{$o->name}->type == "vallist") {
				} else if ($this->fragment->{$o->name}->type == "values") {
				} else if ($this->fragment->{$o->name}->type == "entity") {
				} else if ($this->fragment->{$o->name}->type == "entitylist") {
				} else {
				}
			}
			store::put($this->id, $this);
		}
#dbg(">>> $view: ". json_encode($this));
		$this->init = true;
	}
	private function _add_table($ds, $tn) {
		if (!is_object($this->tables)) $this->tables = (object)[];
		$dt        = (object)[] ;
		$ds        = $this->_ckds($ds);
		$dt->dsrc  = $ds;
		$dt->table = $tn;
		$dt->cols  = $this->_get_table_cols($ds, $tn);
		$this->tables->{$this->_tid($ds,$tn)} = $dt;
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
		$tc = $d->table_columns($tname);
		if ($tc == null || $tc == []) {
			warn("cannot get table $tname");
			return null;
		}
		return $tc;
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
	function quote($f, $v) {
		if ($this->init === false) return false;
#dbg(print_r($this->$this->cols, TRue));
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
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
			return $this->cols->{$f}->column_default;
		}
		return "";
	}
	function datatype($f) {
		if ($this->init === false) return false;
		if (property_exists($this->cols, $f)) {
#dbg($f);
#dbg(json_encode($this->cols));
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
			if ($this->fragment->{$k}->type == "column") {
				if ($v == null) array_push($w, "$k is null"); 
				else            array_push($w, "$k = ". $this->quote($k, $v));
			} else if ($this->fragment->{$k}->type == "reference") {
				#
				# if column is not nullable => add table and hard join:
#dbg(json_encode($this->cols->{$this->fragment->{$k}->cname}));
dbg(json_encode($this));
				#if ($this->tables->{$tid}->cols[$this->fragment->{$k}->cname]->nullable 
				$k = $this->fragment->{$k}->ftname . ".". $this->fragment->{$k}->flname;
				if ($v == null) array_push($w, "$k is null"); 
				else            array_push($w, "$k = ". $this->quote($k, $v));
			}
		}
		$where = " where " . implode(" and ", $w);
		#dbg("select * from $this->table $where");
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

		

dbg($data);
		foreach ($this->fragment as $k => $f) {
#dbg("$k -> " . json_encode($f));
#dbg(":::: "  . $dat->{$k});
			if (property_exists($dat, $k)) {
dbg("---> $k");
				array_push($flds, $f->cname);
				if ($f->type == "column") {
					array_push($vals, $this->quote($f->cname, $dat->{$k}));
				} else if ($f->type == "reference") {
					$v = $this->quote($f->cname, $dat->{$k});
					if ($v == "null" || $v == null) $w = " is null";
					else $w = " = $v";
					$s = "select $f->finame from $f->ftname where $f->flname $w";
					dbg($s);
					$q = new query($s);
					if ($q->nrows() != 1) {
						
						# if table is not RO, then create it:  
					} else {
						$o = $q->obj();
						array_push($vals, $this->quote($f->cname, $o->{$f->finame}));
					}
				} 
			} 
			
		}
		$s = "insert into " . $this->view->tname . " (" . implode($flds, ",") . ") values (" . implode($vals, ",") . ")";
		$q = new query($s);
dbg($s);
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
			err("cannot insert readonly data");
			return '{"status": false; "error": "cannot insert readonly data"}';
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
		return true;
	}
	function query($start = 0, $stop = 25, $sortby = false, $order = false) {
		$s = "select " . implode(', ', $this->slist) . " from ". $this->view->tname . " " . implode(' ', $this->joins);
		if ($sortby !== false) {
			$s .= " order by \"$sortby\"";
			if ($order !== 'up') $s .= " desc";
		}
		$s .= " offset $start limit $stop";
		$q = new query($s);
		return $q->all();	
	}
	function data() {
		if ($this->init === false) return false;
		#dbg( '"'. $this->id . '"');
		return '"'. $this->id . '"';
	} 

	function view() {
		if ($this->init === false) return false;
		return null;
	}
}

