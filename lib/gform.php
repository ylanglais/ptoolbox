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
#if (is_string($req)) dbg($req);
#else dbg(json_encode($req));
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

	$pdat = $prov->data();
#dbg(">>> $pdat");

#	$prec = $prov->req();
	$html .= "<tr><td colspan='3'>";
	$html .= "<input type='button' value='New'    onclick='gform_action(\"$id\", $pdat, \"new\")'/>\n";
	$html .= "<input type='button' value='Update' onclick='gform_action(\"$id\", $pdat, \"update\")'/>\n";
	$html .= "<input type='button' value='Delete' onclick='gform_action(\"$id\", $pdat, \"delete\")'/>\n";
	$html .= "</td></tr></table>\n";
	$html .= "</div>\n";	
	return $html;
}

function gform_ctrl() {
	$a = new args();

	if (!$a->has("id") || !$a->has("data")) {
		print("no data");
	}

	$id   = $a->val("id");
	$data = $a->val("data");

	if (is_string($data)) $data = json_decode($data);
	#dbg("data: " . json_encode($data));

	$prov = new prov($data->prov);
	if ($a->has("action")) {
		$action = $a->val("action");
		if ($action == "new") {
			$prov->put($data->data);
		} else if ($action == "delete") {	
			$prov->del($data->data);
		} else if ($action == "update") {	
			$prov->update($data->data);
		} else {
			dbg_message("unknown action $action");
		}
		#do action

		// reload gui!!!
		
		return;

	} else {
		$action = false;
		#dbg("no action");
	}

	#err(json_encode($data));
	
	print(gform($prov, $data->req));
}
