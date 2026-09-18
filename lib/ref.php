<?php

require_once("lib/args.php");
require_once("lib/query.php");
require_once("lib/util.php");

function ref_get($ref, $skey, $dkey, $val) {
	$sql  = "select $dkey from ref.$ref where $skey = :val";
	$sdat = [ /*":cold" => $dkey, ":table" => "$ref", ":cols" => $skey,*/ ":val" => $val ];

	$q = new query($sql, $sdat);
	if ($q->nrows() == 1) return $q->obj();
	return $q->all();
}

function ref_complete($ref, $key, $like) {
	$sql  = "select $key from ref.$ref where $key like '$like%' limit 25";
	$q = new query($sql);
	$lst = [];
	while ($o= $q->obj()) array_push($lst, $o->$key);
	return json_encode($lst);
}

function ref_complete_city($like) {
	$like = "%$like%";
	$sdat = [ ":like" => $like ];
	$q = new query("select id as id, nom_standard as key, code_postal as linkedval, concat(nom_standard, ' ', code_postal) as val from ref.ville where nom_standard like :like or nom_sans_accent like :like or nom_standard_majuscule like upper(:like) order by 2 limit 25", $sdat);
	return  json_encode($q->all());
}

function ref_complete_cp($like) {
	$like = "$like%";
	$sdat = [ ":like" => $like ];
	$q = new query("select code_postal as key, nom_standard as linkedval, concat(code_postal, ' ', nom_standard) as val from ref.ville where code_postal like :like order by 2 limit 25", $sdat);
	return  json_encode($q->all());
}

function ref_ctrl() {
	$a = new args();
	#$a->dbg();
	if ($a->has("action"))	{
		$act = $a->val("action");
		if (function_exists($act)) {
			$args = mthd_args(null, $act);
			$alst = [];

			foreach ($args as $aa) {
				if (!$a->has($aa->name)) {
					return null;	
				}
				array_push($alst, $a->val($aa->name));
			}
			return $act(...$alst);
		}
	}
	if ($a->has("complete_ctrl")) {
		$cmplctrl = $a->val("complete_ctrl");
		if ($cmplctrl == "complete_country") 
			return (ref_complete('country', 'name', $a->val('like')));
		else if ($cmplctrl == "complete_cp") 
			return (ref_complete_cp($a->val('like')));
		else if ($cmplctrl == "complete_city")
			return ref_complete_city($a->val('like'));
	} 
	return null;
}
