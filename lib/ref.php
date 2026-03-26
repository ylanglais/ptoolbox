<?php

require_once("lib/query.php");

function ref_get($table, $cols, $cold, $val) {
	$sql  = "select :cold from :table where :cols = :val";
	$sdat = [ ":cold" => $cold, ":table" => "$table", ":cols" => $cols, ":val" => "$val" ];

	$q = new qurey($sql, $sdat);
	return $q->all();
}

function ref_ville_from

