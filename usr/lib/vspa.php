<?php
require_once("lib/dbg_tools.php");
require_once("lib/query.php");
require_once("lib/args.php");
require_once("lib/locl.php");
require_once("lib/util.php");

function vspa_search_ref_or_desc($str) {
	$str = strtoupper(trim($str));
	$sdat = [":str" => "%$str%" ];
	$arr = [];
	#dbg("select ref from part where ref like '%$str%'");
	$q = new query("select distinct ref from part where ref like :str limit 25", $sdat );
	while ($o = $q->obj()) array_push($arr, $o->ref);
	$max = count($arr);
	if ($max >= 25) return json_encode($arr);
	$limit = 25 - $max;
	$q = new query("select distinct description from part where description like :str limit $limit", $sdat );
	while ($o = $q->obj()) array_push($arr, $o->description);
	return json_encode($arr);
}

function vspa_list($str) {
	$str = strtoupper(trim($str));
	$sdat = [ ":str" => $str ];
	$qry  = "select p.id as id, o.alias as seller, s.name as supplier, p.ref as ref, p.description as description, p.qty as qty, p.raw_price, p.sell_price from part p, supplier s, seller o where p.supplier = s.id and p.owner_id = o.id and (p.ref = :str or p.description = :str) order by p.ref, p.description";
	$q = new query($qry, $sdat);	
	$all =  $q->all();
	return $all;
}

function vspa_get($id) {
	$sdat = [ ":id" => $id ];
	$qry  = "select p.id as id, o.alias as seller, s.name as supplier, p.ref as ref, p.description as description, p.qty as qty, p.raw_price, p.sell_price from part p, supplier s, seller o where p.supplier = s.id and p.owner_id = o.id and p.id = :id";
	$q = new query($qry, $sdat);	
	return $q->obj();
}
	
function vspa_format($all) {
	$l = new locl();
	/***
	$flds = [ 
		"Référence"      => "ref", 
		"Description"    => "description", 
		"Fournisseur"    => "supplier", 
		"Quantité"       => "qty",
		"Prix catalogue" => "raw_price",
		"Prix de vente " => "sell_price",
	];
	**/
	
	$ret = "<h2>Résultats</h2><table><tr><th>Référence</th><th>Description</th><th>Fournisseur</th><th>Vendeur</th><th>Prix catalogue</th><th>Prix de vente</th><th>Remise</th><th>Quantité</th><tr>\n";

	foreach ($all as $o) {
		$ret .=  "\t<tr onclick='vspa_detail(\"$o->id\")'>" .
				 "<td class='hdr'>$o->ref</td>" .
				 "<td>$o->description</td>" .
				 "<td>$o->supplier</td>" .
				 "<td>$o->seller</td>" .
				 "<td class='num'>". $l->format($o->raw_price)  . "</td>".
				 "<td class='num'>". $l->format($o->sell_price) . "</td>".
				 "<td class='num'>". round(100 - ($o->sell_price / $o->raw_price * 100), 0) . "%</td>".
				 "<td class='num'>$o->qty</td>" .
				 "</tr>\n";	
	}
	$ret .= "</table>";
	return $ret;
}
function vspa_search($str) {
	$all = vspa_list($str);
	return vspa_format($all);
}
function vspa_detail($id) {
	$o = vspa_get($id);
	$r = "<br/><h2>Détail de la piéce</h2>\n<table>";
	foreach ($o as $k => $v) {
		$r .= "<tr><th>$k</th><td>$v</td></tr>\n";
	}	
	$r .= "</table>\n";
	return $r;
}
function vspa_ctrl() {
	$a = new args();
	if ($a->has("input")) {
		$str = $a->val("input");
		if (($r = vspa_search_ref_or_desc($str))  === false) 
			return false;
		//dbg($r);
		print($r);
	} else if ($a->has("qry")) {
		$qry = $a->val("qry");
		print vspa_search($qry);
	} else if ($a->has("partid")) {
		$pid =  $a->val("partid");
		print vspa_detail($pid);
	}
	return null;
}
?>
