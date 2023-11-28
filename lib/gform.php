<?php

require_once("lib/args.php");
require_once("lib/prov.php");
require_once("lib/util.php");

function gform($prov, $req = false, $opts = null) {
	$html = "";

	$id   = gen_elid();
	$html .= "<div class='gform' id='$id'>\n";

	$o = false;
	 
	if ($req !== false) {
		# Load requested data:
		$all = $prov->get($req);
dbg($all);
		if (is_array($all) && count($all) > 0) $o = $all[0];	
	} 
	$flds = $prov->fields();
	$html .= "<table class='form'>\n";
	if (property_exists($opts, "parentid"))
		$html .= "<input type='hidden'  id='__parentid__' value='$opts->parentid'/>";
	$html .= "<input type='hidden' id='opts' value='".json_encode($opts)."'/>";
	$html .= "<input type='hidden' id='__ori__' value='".json_encode($o)  ."'/>";

	foreach ($flds as $f) {
		$html .= "<tr><th><label for='$f'>$f</label></th>";

		$cl = "class=''";
		$vl = "value=''";

		$dv = $prov->defval($f);
		if ($o && property_exists($o, $f)) {
			$dv = $o->$f;
			$vl = "value='". $o->$f."'";
		} else if ($dv != "") {
			$vl = "value=" . $prov->quote($f, $dv);
			$cl = "class='defval'";
		} else if (!$prov->nullable($f) || $prov->iskey($f)) {
			$cl = "class='required'";
		}

		$dt = $prov->datatype($f);
		#$html .= "<td><input name='$f' id='$f' type='text' onchange='gform_(\"$table->module\", this)' $cl $vl/></td></tr>\n";
		
		if ($dt == "bool") {
			$c = "";
			if ($dv === true) $c = "checked";
			$html .= "<td><input name='$f' id='$f' type='checkbox' $c $cl/></td></tr>\n";
		} else if (($fk = $prov->has_fk($f)) !== false) {
			$ft = $fk["ftable"];
			$fc = $fk["fcol"];
			$s = "select distinct $fc as opt from $ft order by 1";
			$html .= "<td><select name='$f' id='$f' $cl>\n";
			$c = "";
			if ($prov->nullable($f) == "YES") {
				if ($dv === null) $c = "selected";
				$html .= "\t<option value='null' $c></option>\n";	
			}
			$q = new query($s);
			while($opt = $q->obj()) {
				$c = "";
				if ($dv === $opt->opt) $c = "selected"; 
				$html .= "\t<option value='$opt->opt' $c>$opt->opt</option>\n";
			}
			$html .= "</select></td>\n";
		} else {
			$html .= "<td><input name='$f' id='$f' type='text' $cl $vl/></td></tr>\n";
		}
	}

	$pdat = $prov->data();
	if (is_object($opts) && property_exists($opts, "ronly") && $opts->ronly !== true) {
		$html .= "<tr><td colspan='3'>";
		$html .= "<input type='button' value='New'    onclick='gform_action(\"$id\", $pdat, \"new\")'/>\n";
		$html .= "<input type='button' value='Update' onclick='gform_action(\"$id\", $pdat, \"update\")'/>\n";
		$html .= "<input type='button' value='Delete' onclick='gform_action(\"$id\", $pdat, \"delete\")'/>\n";
		$html .= "</td></tr>";
	}
	$html .= "</table>\n";
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

	$prov = new prov($data->prov);
	if ($a->has("action")) {
		$action = $a->val("action");
		if ($action == "new") {
			$prov->put($data);
		} else if ($action == "delete") {	
			$prov->del($data);
		} else if ($action == "update") {	
			$prov->update($data);
		} else {
			dbg_message("unknown action $action");
		}
		return;
	} else {
		$action = false;
		#dbg("no action");
	}

	#err(json_encode($data));
	
	print(gform($prov, $data->req, $data->opts));
}
