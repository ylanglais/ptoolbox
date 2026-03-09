<?php

require_once("rcu/dst_db.php");
require_once("rcu/dst_ws.php");
#require_once("rcu/dst_json.php");
#require_once("rcu/dst_csv.php");

class dst {
	static function validate($conf) {
		$greq = [ "medium", "mode", "params" ];
	
		$errs = [];

		if (!property_exists($conf, "dest")) 
			return "missing parameter dest";
		if (!property_exists($conf, "fragments")) 
			return "fragments parameter is missing";

		foreach ($greq as $g) 
			if (!property_exists($conf->dest, $g)) array_push($errs, "parameter $g is missing");

		if (property_exists($conf->dest, "medium") && !in_array($conf->dest->medium, ["ws", "db", "csv", "json"])) 
				array_push($errs, "$conf->dest->medium is not a known medium");

		if (property_exists($conf, "fragments")) {
			foreach($conf->fragments as $fragment) {
				if(!property_exists($fragment, "table")) array_push($errs, "table is not a known fragments");
			}
		}
						
		$cname = "dst_" . $conf->dest->medium;
		if (($r = $cname::validate($conf)) != true) 
			return array_merge($errs, $r);
		return $errs == [] ? true : $errs;
	}

	function __construct($conf) {
		$cname         = "dst_" . $conf->dest->medium;
		$this->dst     = new $cname($conf);
	}

	function mode() {
		return $this->dst->mode();	
	}
	function set($field, $value) {
		return $this->dst->set($field, $value);
	}
	function link($field, $arr) {
		return $this->dst->link($field, $arr);
	}
	function last_error() {
		return $this->dst->last_error();
	}
	function line_validate() {
		return $this->dst->line_validate();	
	}
	function flux_validate() {
		return $this->dst->flux_validate();
	}
	function map_dst($src,$value,$mode){
		return $this->dst->map_dst($src,$value,$mode);
	}
	function map_link($val_tab_ref,$origine = ""){
		return $this->dst->map_link($val_tab_ref,$origine);
	}
	function map_to_ref($dst,$field,$value){
		return $this->dst->map_to_ref($dst,$field,$value);
	}
	function map_vref($dst,$type,$value,$src_1,$src_1_val,$src_2){
		return $this->dst->map_vref($dst,$type,$value,$src_1,$src_1_val,$src_2);
	}

}
