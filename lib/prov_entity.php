<?php
require_once("lib/prov.php");

class prov_entity {
	function __construct($entity = null, $filter = null) {
		$this->type   = "entity";
		$this->name   = $name = $entity;
		$this->tables = [];
		$this->fields = [];
		$this->qry    = (object) [];
		$this->slist  = [];
		$this->joins  = [];
		$this->keys   = [];
		if (is_string($entity) && substr($entity, 0, 14) == "__prov_entity_") { 
			if ($o = store::get($entity) !== false) { 
				foreach ($o as $k => $v) {
					$this->$k = $v;
				}
				dbg("$entity restored");
			} else {
				err("cannot restore $d");
				print("<h2>Bad data</h2></div>");
				exit();
			}
		} else {
			$q = new query("select * from param.entity where name = '$name'");
			if (($o = $q->obj()) === false) {
				err("no entity named '$name'");
				return;	
			}
			$this->ent = (object)[];
			foreach ($o as $k => $v) $this->ent->$k = $v;

			$this->ent->dsrc = $this->_ckds($this->ent->dsrc);
			$this->entid     = $this->ent->dsrc . "__" . $this->ent->tname;

			$this->_add_table($this->ent->dsrc, $this->ent->tname);

			$this->fragment = [];
			$this->refs     = [];
			$q = new query("select * from param.fragment where entity = '$name'");
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
			while ($o = $q->obj()) {
				$this->fragment[$o->name] = (object)[];
				foreach ($o as $k => $v) { 
					if ($k != 'name' && $v != null) $this->fragment[$o->name]->$k = $v;
				}
				if ($this->fragment[$o->name]->type == "column") {
					$this->cols[$o->name] = $this->tables[$this->entid]->cols[$o->cname];	
					array_push($this->fields, $o->name);
					array_push($this->slist, $this->ent->tname . ".$o->cname as \"$o->name\"");
				} else if ($this->fragment[$o->name]->type == "reference") {
					$tid = $this->_tid($o->fsrc, $o->ftname);
					$this->_add_table($o->fsrc, $o->ftname);
					$this->cols[$o->name] = $this->tables[$tid]->cols[$o->flname];
					array_push($this->fields, $o->name);
					array_push($this->slist, "$o->ftname.$o->flname as \"$o->name\"");
					array_push($this->joins, "left join $o->ftname on " . $this->ent->tname . ".$o->cname = $o->ftname.$o->finame");
				} else if ($this->fragment[$o->name]->type == "vallist") {
				} else if ($this->fragment[$o->name]->type == "entity") {
				} else if ($this->fragment[$o->name]->type == "entity") {
				} else {
				}
			}
		}
		store::put($this->data(), $this);
	}
	private function _add_table($ds, $tn) {
		if (!is_array($this->tables)) $this->tables = [];
		$dt        = (object)[] ;
		$ds        = $this->_ckds($ds);
		$dt->dsrc  = $ds;
		$dt->table = $tn;
		$dt->cols  = $this->_get_table_cols($ds, $tn);

		$this->tables[$this->_tid($ds,$tn)] = $dt;
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

	function fields() {
		return $this->fields;
	}
	function keys() {
		return $this->keys;
	}
	function type() {
		return $this->type;
	}
	function name() {
		return $this->name;

	}
	function get($req) {
	}
	function put() {
	}
	function del() {
	}
	function query($start = 0, $stop = 25, $sortby = false, $order = false) {
		$s = "select " . implode(', ', $this->slist) . " from ". $this->ent->tname . " " . implode(' ', $this->joins) . " offset $start limit $stop";
		$q = new query($s);
		return $q->all();	
	}
	function count($filter = null) {
		$s = "select count(*) from ". $this->ent->tname . " " . implode(' ', $this->joins) ;
		$q = new query($s);
		$o = $q->obj();
		if ($o === false || !is_object($o) || !property_exists($o, "count")) return ($this->count = 0);
		return ($this->count = $o->count);
	}	

	function data() {
		return "__prov_entity_" . $this->name;
	} 
}

