<?php

require_once("lib/args.php");
require_once("lib/prov.php");
require_once("lib/util.php");

function gform($prov, $req = false, $actions = null) {
	$html = "";

	$id   = gen_elid();
	$html .= "<div class='gform' id='$id'>\n";

	$p = new prov($prov);

	$o = false;
	 
	if ($req !== false) {
		$o = $p->get($req);
	} 

	$flds = $p->fields();


	$html .= "<table class='form'>\n";
	foreach ($flds as $f) {
		$html .= "<tr><th><label for='$f'>$f</label></th>";

		$cl = "class=''";
		$vl = "value=''";

		if ($o && property_exists($o, $f)) {
			$vl = "value='". $o->$f."'";
		} else if (($rr = $p->defval($f)) != "") {
			$vl = "value=" . $p->quote($f, $rr);
			$cl = "class='defval'";
		} else if (!$p->nullable($f) || $p->iskey($f)) {
			$cl = "class='required'";
		}
		#$html .= "<td><input name='$f' id='$f' type='text' onchange='gform_(\"$table->module\", this)' $cl $vl/></td></tr>\n";
		$html .= "<td><input name='$f' id='$f' type='text' $cl $vl/></td></tr>\n";
	}
	$html .= "<tr><td colspan='3'>";
	
	return $html;
}

function gform_ctrl() {
	$a = new args();

	if (!$a->has("id") || !$a->has("data")) {
		print("no data");
	}

	$id   = $a->val("id");
	$data = $a->val("data");

	if ($a->has("action")) {
		$action = $a->val("action");

		#do action



	} else $action = false;

#err(json_encode($data));
	
	print(gform($data["prov"], $data["req"]));

	

}
