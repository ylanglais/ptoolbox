<?php

require_once("lib/dbg_tools.php");
require_once("rcu/dbmeta.php");
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/util.php");

class dst_db {
	private $last_err = null;
	private $table = "";
	private $vref  = [];
	private $map_exception =  '/^map_link\(.*|^map_vlink\(.*|^map_to_ref\(.*|^map_vref\(.*|^map_dst\(.*/';
	static function validate($conf) {
		return true;
	}
	function __construct($conf) {
		$this->buf  = [];
		$this->table_key = "";
		$this->ids  = [];
		$this->frgs = [];
		$this->dm   = [];
		$this->odb  = false;

		foreach ($conf->fragments as $f) { 
			$this->frgs[$f->table] = $f; 
			if (($p = strpos($f->table, ".")) !== false) { 
				$schema = substr($f->table, 0, $p);
				$table  = substr($f->table, $p + 1);
			} else {
				$schema = $conf->dest->params->schema;
				$table  = $f->table;
			}
			$this->dm[$f->table] = new dbmeta($schema, $table, (array) $conf->dest->params);
			if ($this->odb === false) $this->dm[$f->table]->db_connection();
			$cols[$f->table] = $this->dm[$f->table]->columns();
		}
                
		#
		# **IMPROVEMENT REQUIRED**	
		#
		$this->fragments = $conf->fragments;                   
		#$this->main_table_get($conf->fragments);
		#$this->fields = [];

		foreach ($conf->dest->params as $p => $v) $this->$p = $v;

		#if ($conf->dest->mode != "line") 
		$this->mode = "line";

		foreach ($conf->map as $map) {

			[ $schema, $table, $field ] = $this->dl_to_stc($map->dest);
			if (property_exists($map, "link")) {
				if ($table != "") $table = "$table.$field"	;
				else $table = $field;
				$field = "";
			} else if ($schema != "") $table = "$schema.$table";
			
			preg_match($this->map_exception, $map->dest, $m);

			if ($field != "" && !in_array($field, $cols[$table]) && count($m) == 0) {
				_warn("ignore field $field since not present in destination table $table");
				_warn("cols are : " . json_encode($cols[$table]));
			} else if (count($m) > 0) {
				$re = '/(map_[a-z_]*)\(((?>[^()]|(?R))*)\)/'; #<?
				preg_match($this->re, $map->dest, $m);
				$param = explode(",",$m[2]);
				$this->tab_link = $param[0];
			} 
			# else {
				#array_push($this->fields, $map->dest);
			#}
		}
	}
	function dl_to_stc($datalink) {
		if (preg_match("/^(([^.]*)\.)?([^.]*)\.(.*)$/", $datalink, $m)) {
			return [ $m[2], $m[3], $m[4] ];	
		}
		return ["", "", "$datalink" ];
	}
	function mode() {
		return $this->mode;	
	}
	function genid($table, $nullval = false) {
		if (property_exists($this->frgs[$table], "id")) { 
			if ($nullval !== false) {
				$id = null;
			} else if ($this->frgs[$table]->id->type == "uuid") {
				$id = $this->dm[$table]->check($this->frgs[$table]->id->field, gen_uuid());
			} else {
				_err("Unsupported id type");
				return false;
			}

			if (!array_key_exists($table, $this->ids))
				$this->ids[$table] = [];
			array_push($this->ids[$table], $id);

			if (!array_key_exists($table, $this->buf))
				$this->buf[$table] = []; 
		
			$n = count($this->buf[$table]);
			$this->buf[$table][$n] = (object) [];
			$this->buf[$table][$n]->{$this->frgs[$table]->id->field} = $id;

			return $id;
		}
		return false;
	}
	function set($datalink, $value) {
		[ $schema, $table, $field ] = $this->dl_to_stc($datalink);
		if ($schema != "") $table = "$schema.$table";
		
		# determine table name & field:
		if (!array_key_exists($table, $this->frgs)) {
			_err("table $table doesn't exist");
			return false;
		}

		if (($val = $this->dm[$table]->check($field, $value)) === false) {
			#_info("$table.$field is null while shouldn't");
			$this->genid($table, true);
			$n = count($this->buf[$table]) - 1;
			$this->buf[$table][$n]->ignore = true;
			return true;
		}
	
		if (!array_key_exists($table, $this->buf))  {
			# new instance of table:
			$id = $this->genid($table);
		} else {
			# we already have an instance:
			$l = count($this->buf[$table]);
			# check if we need to create a new one:
			if (property_exists($this->buf[$table][$l - 1], $field))
				$this->genid($table);
		}
		# new data for current occurence:
		$n = count($this->buf[$table]) - 1;
		$this->buf[$table][$n]->$field = $val;

		if ($val === false)	{
			$this->buf[$table][$n]->ignore = true;
			$this->ids[$table][$n] = null;
		}

		return true;
	}
	function link($table, $arr) { 
		#
		# check link table:
		if (!array_key_exists($table, $this->frgs)) {
			_err("table $table not a defined fragment");
			return false;
		}

		$this->genid($table);
		$n = count($arr);
		$m = count($this->frgs[$table]->link);
		if ($n > $m ) {
			_warn("number of link arguments ($n) is greater than defined in $table fragment ($m), only the $m 1st link values will be taken into account");
			$n = $m;
		}

		$l = count($this->buf[$table]) - 1;

		$buf = end($this->buf[$table]);
		for ($i = 0; $i < $n; $i++) {
			if (property_exists($this->frgs[$table]->link[$i], "table")) {
				$tbl   = $this->frgs[$table]->link[$i]->table;
				$this->buf[$table][$l]->$tbl = $this->dm[$table]->check($tbl, $arr[$i]);
			}
			$field = $this->frgs[$table]->link[$i]->id;
			$this->buf[$table][$l]->$field = ($t = end($this->ids[$arr[$i]]));
			if ($t == null || $t == 'null') {
				$this->buf[$table][$l]->ignore = 'true';
				return true;
			}	
		}
		return true;	
	}
	function last_error() {
		return $this->last_err;
	}
	function line_validate() {
		#_dbg(json_encode($this->buf));
		$this->last_err = null;
		$q = new query("begin TRANSACTION", $this->odb);
		$stat = "ok";
		foreach ($this->fragments as $f) {
			foreach ($this->buf[$f->table] as $b) {
				if (property_exists($b, "ignore")) continue;
				$b = (array) $b;
				$s = "insert into $f->table (" . implode(",", array_keys($b)) . ") values (" . implode(",", $b) . ")";
				$q = new query($s, $this->odb);
				$this->last_err = $r = $q->error();
				if ($r !== false) {
					_err("on \"$s\" : $r");
					$stat = "ko";
					break 2;	
				}
			}
		}
		$this->buf = [];
		$this->ids = [];
		if ($stat == "ko") {
			$q = new query("rollback", $this->odb);
			return false;	
		}
		$q = new query("commit", $this->odb);
		return true;
	}
	function flux_validate() {
		return true;
	}
     
	function main_table_get($fragments){
		foreach($fragments as $fragment){
			if($fragment->type == "main")
				$this->table = $fragment->table;
		}
		return false;            
	}
/**
	function map_wlink_insert($id,$infos){
		for($i=0;$i< sizeof($infos->wlink->fields);$i++){
			if(isset($infos->wlink->fields[$i+1])){
				$tab_id[$infos->wlink->fields[$i]."_".$infos->wlink->fields[$i+1]]['id'] = $infos->wlink->fields[$i+1];
				$tab_id[$infos->wlink->fields[$i]."_".$infos->wlink->fields[$i+1]]['table'] = $infos->wlink->fields[$i];
			}
		}
		$i = array_search("vref", array_column($this->fragments, 'type'));
		$id_name_tab_vref = $this->fragments[$i]->id->field;
		$values = [];
		$i=0;
		foreach($this->vref as $data){
			foreach($data as $k => $v){
				if(in_array($k, $infos->wlink->fields) || $k == $id_name_tab_vref || in_array($k, array_keys($tab_id))){
					if($k == $id_name_tab_vref) $values[$i][$infos->wlink->id->field] = $v ;
					else if(in_array($k, array_keys($tab_id))){ 
						$j = array_search($data[$tab_id[$k]['table']], array_column($this->fragments, 'table'));
						$r = $this->get_infos($data[$tab_id[$k]['table']],$this->fragments[$j]->id->field,$this->fragments[$j]->ref,$v);
						if($r->nrows() == 1) 
							$values[$i][$tab_id[$k]['id']] = $r->obj()->id;                            
					}else if($infos->wlink->fk_main == $k) $values[$i][$k] = $id;                        
					else $values[$i][$k] = $v ;                        
				}
			}
			$i++;                
		}

		
		for($i=0;$i<sizeof($values);$i++)
			$values[$i][$infos->wlink->fk_main] = $id;
		
		$query = "insert into $this->schema.$infos->table (".implode(',',array_keys($values[0])).") values ";

		$data = "";
		foreach($values as  $v){
			if(empty($data)) $data = " (".sprintf("'%s'", implode("','", $v ) ) .")";
			else $data .= ", (".sprintf("'%s'", implode("','", $v ) ) .")";
		}            
		$req = new query($query.$data, $this->odb);
		$res = $req->error();
		if($res !== false){
			_warn("ignore insert to $this->schema.$infos->table query error");
		}
		unset($this->vref);
	}
	
	function map_link_insert($id,$values){
		$fragment = $this->get_from_fragment('link');
		
		$q = "";
		foreach($values as $val){
			$r = $this->get_infos($fragment[0]['to_table'],$fragment[0][$fragment[0]['to_table']]['id'],$fragment[0]['to_ref'],$val);
			if($r->nrows() > 0) {
				if(empty($q)) $q .= "('$id','".$r->obj()->id."')";
				else $q .= ", ('$id','".$r->obj()->id."')";
			}else{
				_warn("ignore field $ref = $value since not present in destination table ref");
			}
		}
		if(!empty($q)){
			$query = "insert into $this->schema.$this->tab_link (".$fragment[1]['from_id'].",".$fragment[1]['to_id'].") VALUES $q";   
			$req = new query($query, $this->odb);
			$res = $req->error();
			if($res !== false){
				_warn("ignore insert to $this->tab_link query error");
			}
		} else {
			_warn("ignore insert to $this->tab_link no value");
		}

	}
	function map_vlink_insert($id,$values,$origine){
		$fragment = $this->get_from_fragment('vlink');
		$q = "";
		$i=0;

		foreach($values as $val){
			$r = $this->get_infos($origine[$i],$fragment[0][$origine[$i]]['id'],$fragment[0][$origine[$i]]['ref'],$val);
			if($r->nrows() > 0) {
				if(empty($q)) $q .= "('$id','".$origine[$i]."','".$r->obj()->id."')";
				else $q .= ", ('$id','".$origine[$i]."','".$r->obj()->id."')";
			} else {
				_warn("ignore field $ref = $value since not present in destination table ref");
			}
			$i++;
		}
		
		if(!empty($q)){
			unset($this->val_tab_ref);
			if(isset($this->origine_tab)) unset($this->origine_tab);
			$query = "insert into $this->schema.$this->tab_link(".$fragment[1]['from_id'].",".$fragment[1]['to_extra_data'].",".$fragment[1]['to_id'].") VALUES $q";
			$req = new query($query, $this->odb);
			$res = $req->error();
			if($res !== false){
				_warn("ignore insert to $this->tab_link query error");
			}
		}else{
			_warn("ignore insert to $this->tab_link no value");
		}
		
	}
	
	function map_link($val_tab_ref, $origine = ""){
		$this->val_tab_ref[] = $val_tab_ref;
		if(!empty($origine))
			$this->origine_tab[] = $origine;
	}
	
	function map_to_ref($dst,$field,$value){
		$this->ref =  new stdClass();
		$this->ref->{$dst}[$field] = $value;
	}
			
	function map_dst($src,$value,$mode =""){
		$fields = [];
		$values = [];
		foreach($this->fragments as $fragment){
			foreach($fragment as $k => $v){
				if($k == "table" && $src == $v){
					$ref = $fragment->ref;
					$id  = $fragment->id->field;
					$type = $fragment->id->type;
				}
			}
		}
		
		$q = $this->get_infos($src,$id,$ref,$value);
		if($q->nrows() == 0){
			if(strtoupper($mode) == "RW" && !empty($mode)){
				switch($type){
					case "uuid":                            
						array_push($values, "'".gen_uuid()."'");
						array_push($fields, $id);
					case "seq":
						break;
					case "max":
						$sql = "(select coalesce(max($id)+1,1) as id from $this->schema.$src)";
							array_push($values, $sql);
							array_push($fields, $id);
						break;
				}
				if(is_array($value)){
					foreach($value as $f => $v){
						array_push($fields, $f); 
						array_push($values, "'".$v."'"); 
					}
				}else{
					array_push($fields, $ref); 
					array_push($values, "'".$value."'"); 
				}
				if(isset($this->ref->{$src})){
					foreach($this->ref->{$src} as $k => $v){
						array_push($fields, $k); 
						array_push($values, "'".$v."'");
					}                        
				}
				
				$query = "insert into $this->schema.$src(" . implode(",", $fields) . ") values(" . implode(",", $values) . ") RETURNING $id as id";
				$q = new query($query, $this->odb);
				$r = $q->error();
				if($r === false)
					return $q->obj()->id;
				else
					return $r;
			}else{
				if(!is_array($value)) _warn("ignore field $ref = $value since not present in destination table ref");
				else _warn("ignore field $ref = ".$value[$ref]." since not present in destination table ref");
			}
		}else{
			return $q->obj()->id;
		}
		
	}
	
	function map_vref($dst,$type,$value,$table_1,$table_1_val,$table_2){
		$fields = [];
		$values = [];
		$return_f= [];
		foreach($this->fragments as $fragment){
			foreach($fragment as $k => $v){
				if($k == "table" && $dst == $v){
					$f_ref       = $fragment->ref;
					$f_id        = $fragment->id->field;
					$f_type      = $fragment->id->type;
					$f_fields    = $fragment->fields;
					$f_field_val = implode("_",$fragment->field_val);
				}
			}
		}
		if(!is_null($value) && !empty($value) && strtoupper($value) != "NULL"){
			$params = array($f_fields[0] => $type,$f_fields[1] => $value,$f_fields[2] => $table_1,$f_fields[3] => $table_2);
			
			switch($f_type){
				case "uuid":                            
					array_push($values, "'".gen_uuid()."'");
					array_push($fields, $f_id);
					array_push($return_f, $f_id);
				case "seq":
					array_push($return_f, $f_id);
					break;
				case "max":
					$sql = "(select coalesce(max($f_id)+1,1) as id from $this->schema.$dst)";
						array_push($values, $sql);
						array_push($fields, $f_id);
						array_push($return_f, $f_id);
					break;
			}                
			array_push($return_f, ...array_keys($params));  
				
			$q = $this->get_infos($dst,$f_id,$f_ref,$params,$return_f);

			if($q->nrows() == 0){
				array_push($values, ...array_values($params));
				array_push($fields, ...array_keys($params));  
				$query = "insert into $this->schema.$dst(" . implode(",", $fields) . ") values(" . sprintf("'%s'", implode("','", $values ) ) . ") RETURNING ";                    
				$query .= implode(",", $return_f);
				$q = new query($query, $this->odb);
				$r = $q->error();

				if($r === false){                        
					$data = $q->data();
					foreach($data as $k => $v)
						$this->vref[$data[$f_id]][$k] = $v;
					
					$this->vref[$data[$f_id]][$f_field_val] = $table_1_val;
				}
				else
					return $r;
			}else{
				$data = $q->data();
				foreach($data as $k => $v)
					$this->vref[$data[$f_id]][$k] = $v;
				
				$this->vref[$data[$f_id]][$f_field_val] = $table_1_val;                                                      
				return $data[$f_id];
			}
		}   
	}
	
	function get_infos($table,$id,$field,$value,$custom_fields=""){
		$cond= "";
		if(is_array($value)){
			foreach($value as $f => $v){
				if(empty($cond)) $cond .= "$f = '$v'";
				else $cond .= "and $f = '$v'";
			}
		}else $cond= "$field = '$value'";
		if(empty($custom_fields)) $query= "select $id as id from $this->schema.$table where $cond";       
		else $query= "select ". implode(",", $custom_fields)." from $this->schema.$table where $cond";       
		$r = new query($query, $this->odb);
		return $r;
	}
	
	function get_from_fragment($type){
		foreach($this->fragments as $fragment){
			foreach($fragment as $k => $v){
				if($k == "table"){
					if(isset($fragment->id->field))
					$tables[$v]['id'] = isset($fragment->id->field) ? $fragment->id->field : "";
					$tables[$v]['ref'] = isset($fragment->ref) ? $fragment->ref : "";
				}
				
				if($k == "table" && $this->tab_link == $v){
					//print_r($fragment->{$type} );exit();
					//foreach($fragment->{$type} as $fragment){
					//	$link[]
					//}
					$link['from_table'] =   isset($fragment->{$type}->from_table) ? $fragment->{$type}->from_table : "";
					$link['to_table']   =   isset($fragment->{$type}->to_table)  ? $fragment->{$type}->to_table : "";
					$link['from_id']    =   isset($fragment->{$type}->from_id)  ? $fragment->{$type}->from_id : "";
					$link['to_id']      =   isset($fragment->{$type}->to_id) ? $fragment->{$type}->to_id : "";
					$link['from_ref']   =   isset($fragment->{$type}->from_ref) ? $fragment->{$type}->from_ref : "";
					$link['to_ref']     =   isset($fragment->{$type}->to_ref) ? $fragment->{$type}->to_ref : "";
					$link['to_extra_data']     =   isset($fragment->{$type}->to_extra_data) ? $fragment->{$type}->to_extra_data : "";
				}
			}
		}
		return array($tables,$link);
	}
*****/
}
