<?php
require_once("lib/prov.php");
require_once("lib/db.php");
require_once("lib/query.php");

class prov_entity {
	function __construct($entity = null, $filter = null) {
		$this->id    = $entity;
		$this->init     = false;
		$this->type     = "entity";
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
		$this->ent      = false;
		$restored       = false;

#dbg(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)));
#dbg($entity);
		if (is_string($entity) && substr($entity, 0, 14) == "__prov_entity_") { 
			if (($o = store::get($entity)) !== false) { 
				if (is_array($o) || is_object($o)) {
					foreach ($o as $k => $v) {
						$this->$k = $v;
					}
					##dbg("$entity restored");
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
			$perm = get_perm("entity", $entity);
			if ($perm != 'RONLY' && $perm != 'ALL') {
				$s =  "Attempted access to entity $entity without due permission";
				audit_log("SECURITY", $s);
				err("SECURITY: " . get_user(). " $s");
			}
			$this->perm = $perm;

			$q = new query("select * from param.entity where name = '$entity'");
			if (($o = $q->obj()) === false) {
				err("no entity named '$entity'");
				return;	
			}
			$this->name = $entity;
			$this->ent = (object)[];
			foreach ($o as $k => $v) $this->ent->$k = $v;

			$this->ent->dsrc = $this->_ckds($this->ent->dsrc);
			$this->id     = "__prov_entity__" . $this->name;

			$this->_add_table($this->ent->dsrc, $this->ent->tname);

			$this->refs     = [];
			$q = new query("select * from param.fragment where entity = '$this->name'");
			#
			# entity:
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
			$keys = (array) $db->table_keys($this->ent->tname);

			while ($o = $q->obj()) {
				$this->fragment->{$o->name} = (object)[];
				foreach ($o as $k => $v) { 
					#if ($k != 'name' && $v != null) 
					$this->fragment->{$o->name}->{$k} = $v;
				}
				if ($this->fragment->{$o->name}->type == "column") {
					$this->cols->{$o->name} = $this->tables->{$this->id}->cols[$o->cname];
					array_pysh($this->fields, $o->name);
					array_push($this->slist, $this->ent->tname . ".$o->cname as \"$o->name\"");
					if (in_array($o->name, $keys)) array_push($this->keys, $o->name);
				} else if ($this->fragment->{$o->name}->type == "reference") {
					$tid = $this->_tid($o->fsrc, $o->ftname);
					$this->_add_table($o->fsrc, $o->ftname);
#dbg(print_r($this->tables->{$tid}, true));
#dbg("--->>> $o->flname");
#dbg("---<<< $o->name");
#dbg($this->cols);
					$this->cols->{$o->name} = $this->tables->{$tid}->cols[$o->flname];
					if (in_array($o->cname, $keys)) array_push($this->keys, $o->name);
					array_push($this->fields, $o->name);
					array_push($this->slist, "$o->ftname.$o->flname as \"$o->name\"");
					array_push($this->joins, "left join $o->ftname on " . $this->ent->tname . ".$o->cname = $o->ftname.$o->finame");
				} else if ($this->fragment->{$o->name}->type == "vallist") {
				} else if ($this->fragment->{$o->name}->type == "values") {
				} else if ($this->fragment->{$o->name}->type == "entity") {
				} else if ($this->fragment->{$o->name}->type == "entitylist") {
				} else {
				}
			}
			store::put($this->id, $this);
		}
#dbg(">>> $entity: ". json_encode($this));
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
		return $d->table_columns($tname);
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
/***
		if ($this->count !== false) return $this->count;
		$sql = "select count(*) as count from $this->table " . $this->_whereclause();
		$q = new query($this->db, $sql);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
***/
		$s = "select count(*) from ". $this->ent->tname . " " . implode(' ', $this->joins) ;
		$q = new query($s);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}
	function get($req) {
		if ($this->init === false) return false;
		$w = [];
		foreach ($req as $k => $v) {
			if ($this->fragment->{$k}->type == "column") {
				if ($v == null) array_push($w, "$k is null"); 
				else            array_push($w, "$k = ". $this->quote($k, $v));
			} else if ($this->fragment->{$k}->type == "reference") {
				$k = $this->fragment->{$k}->ftname . ".". $this->fragment->{$k}->flname;
				if ($v == null) array_push($w, "$k is null"); 
				else            array_push($w, "$k = ". $this->quote($k, $v));
			}
		}
		$where = " where " . implode(" and ", $w);
		#dbg("select * from $this->table $where");
		$s = "select " . implode(', ', $this->slist) . " from ". $this->ent->tname . " " . implode(' ', $this->joins) . " $where";
		$q = new query($s);		
		return $q->obj();

	}
	function put() {
		if ($this->init === false) return false;
	}
	function update($req) {
		if ($this->init === false) return false;
	}
	function del() {
		if ($this->init === false) return false;
	}
	function query($start = 0, $stop = 25, $sortby = false, $order = false) {
		$s = "select " . implode(', ', $this->slist) . " from ". $this->ent->tname . " " . implode(' ', $this->joins) . " offset $start limit $stop";
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

