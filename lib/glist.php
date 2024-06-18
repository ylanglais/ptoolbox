<?php

require_once("lib/args.php");
require_once("lib/util.php");
require_once("lib/prov.php");
require_once("lib/locl.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/session.php");

function glist_dopts($dopts, $offset= false, $page = false) {
	$d = $dopts;
	if ($offset !== false) $d["start"] = $offset;
	if ($offset !== false) $d["page"]  = $page;

	return json_encode($d);
}

function glist_user_pref_get($prov) {
	$uid = get_user_id();
	$pid = $prov->id();
	$q =  new query("select * from param.glist where user_id = $uid and provider = '$pid'");
	$opts = [];
	while ($o = $q->obj()) {
		$opts["sort"] = $o->sortby;
		$opts["order"] = $o->orderby;
	}
	return $opts;
}

function glist_user_pref_save($prov, $opts) {
	if (!is_object($prov)) {
		$prov = new prov($prov);
	}
	if (is_object($opts)) $opts = (array) $opts;
	if (!is_array($opts) || !array_key_exists("sort", $opts) || !array_key_exists("order", $opts)) {
		err("bad opts (". json_encode($opts) .")");
		return;
	}

	$sb = $opts["sort"];
	$ob = $opts["order"];
	$uid = get_user_id();
	$pid = $prov->id();
	if (($o = glist_user_pref_get($prov)) != []) {
		new query("update param.glist set sortby = '$sb', orderby = '$ob' where user_id = '$uid' and provider = '$pid'");
	} else {
		new query("insert into param.glist (user_id, provider, sortby, orderby) values ($uid, '$pid', '$sb', '$ob')"); 
	}
}

function glist($prov, $opts = []) {
	#
	# Prepare options:
	$dopts = [ 
		"start" 	=> 0, 
		"page" 		=> 25, 
		"ln" 		=> true, 
		"hdr" 		=> true, 
		"ftr" 		=> true, 
		"sort" 		=> false, 
		"order" 	=> "up", 
		"id" 		=> false , 
		"ronly" 	=> false, 
		"msel" 		=> false, 
		"parentid"	=> false, 
		"gform_id" 	=> false 
	];

	# first use, try to load user prefs for this provider:
	if ($opts == [] || !array_key_exists("start", $opts))  $opts = array_merge($opts, glist_user_pref_get($prov));
	else if (is_object($opts)) $opts = (array) $opts; 

	if (is_array($opts)) {
		foreach ($dopts as $k => $v) {
			if (!array_key_exists($k, $opts)) $opts[$k] = $v;
		}
	} else $opts = $dopts;

	# 
	# Gen identifier if not present:
	if ($opts["id"] === false) $opts["id"]  = "glist_" . gen_elid();		
	$opts["parentid"] = $id = $opts["id"];

	$html = "<input type='hidden' id='".$id."_opts' value='". json_encode($opts) . "'/>\n";
	#
	# Prepare table:
	$html .= "<table class='glist' id='$id'>\n";

	#
	# Check permission:
	$perm = $prov->perm(); 
	if ($perm == 'NONE') {
		err("no permission");
		return $html . "<tr><td> No data </td></tr></table>\n";
	}	
	#
	# Check if rights are more restricted than asked:
	if ($opts["ronly"] == false && $perm == 'RONLY') $opts["ronly"] = true;

	#
	# Check if provider is set/data present:
	if (!is_object($prov))  return $html . "<tr><td> No data </td></tr></table>\n";

	#
	# Get fields:
	$fields = $prov->fields();
	if ($fields === false) return $html . "<tr><td> No data </td></tr></table>\n";
	$nf     = count($fields);

	$pdat = $prov->data();
	$keys = $prov->keys();
	if ($keys == []) $keys = $fields;

	#
	# Compute header / footer only if required:
	if ($opts["hdr"] || $opts["ftr"]) { 
		#$hdr = "<tr onclick='glist_popup()'>";
		$hdr = "<tr>";

		if ($opts["ronly"] === false) {
			$hdr .= "<th><input id='cktoggle' type='checkbox' onclick='event.cancelBubble=true;glist_toggle_selected(\"gmsel_".$prov->name()."\")'<th>";
		}
		
		#
		# Line numbering if required:
		if ($opts["ln"]) $hdr .= "<th>#</th>";

		$d = glist_dopts($opts);

		foreach ($fields as $f) {
			$extra = "";
			if ($f == $opts["sort"]) {
				$extra .= ' sel ';
				$extra .= $opts["order"];
			}
			$hdr .= "<th>$f<div class='sort$extra' id='sort_$f' onclick='glist_sort(this, $pdat, $d, \"$f\")'></div></th>";
		}
		$hdr .= "</tr></thead>\n";
	} else $hdr = "";

	#
	# Compute header / footer only if required:
	#
	# add header if required:
	if ($opts["hdr"])  $html .= $hdr;

	#
	# Add localization support:
	$locl = new locl();

	$nr = $so = $pl = 0;

	#
	# Compute header / footer only if required:
	#
	# Get data from provider from start offset + nb lines per page:
	$all = $prov->query($opts["start"], $opts["page"], $opts["sort"], $opts["order"]);
	if (!is_array($all) || (count($all) == 0)) {
		#
		# No data present: 
		$n = $nf;
		if ($opts["ronly"] === false) $n++;
		if ($opts["ln"])              $n++;
		$html .= "<tr><td class='hdr' colspan='$n'>Pas de données</td></tr>\n";
		$npages = 0;
	} else {
		#
		# Compute paging:
		$pl = $opts["page"];
		$so = $opts["start"];
		$nr = $prov->count();
		$np = ceil($nr / $pl);
		$lo = ($np - 1) * $pl;

		if ($so <   1) $so = 0;
		if ($so > $lo) $so = $lo;

		$no = $so + $pl;
		$po = $so - $pl;
		$sp = 0;
		$cp = intdiv($so, $pl) + 1;	
		
		$i = $so;
#		$html .= "<tbody>";

		#
		# Print all result from start offset (so) + page count (pl):
		foreach ($all as $o) {
			$i++;

			$qry = [];
			if ($opts["gform_id"] !== false) {
				foreach ($keys as $k) {
					if (!property_exists($o, $k)) {
						if (!property_exists($o, "_hidden_$k")) {
							break;
						}
						$qry[$k] = $o->{"_hidden_$k"};
					} else {
						$qry[$k] = $o->$k;
					}
				}
			}

			$html .= "<tr onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")'";
			if ($qry != []) {
				$vdata = '{ "prov": '. $pdat . ', "req": '. json_encode($qry) . ', "opts": '.json_encode($opts) .'}';
				$html .= " onclick='glist_view(\"".$opts["gform_id"]."\", $vdata)'";
			} 
			$html .=">";

			$__id = urlencode(json_encode($qry));
		
			if ($opts["ronly"] === false) $html .= "<td class='ckbox'><input id='ck $__id' class='gmsel_".$prov->name()."' type='checkbox' onclick='event.cancelBubble=true;'/></td>";
			if ($opts["ln"]) $html .= "<td class='num'>".($i)."</td>";
			
			foreach ($fields as $k) {
				$cl = "";
				$type = $prov->datatype($k);
				if (property_exists($o, $k)) { 
					if ($type == "bool") {
						$cl = "class='ckbx'";
						$v = $o->$k;
						if ($v == 't' || $v == true) $v = "&#9745;" ; 
						else $v = "&#9744;";
					} else {
						$v = $locl->format($o->$k);
						if (is_float($o->$k) || is_numeric($o->$k) || substr($o->$k, -1) == "%") $cl = "class='number'";
					}
				} else {
					$v = "";
				}
				$html .= "<td $cl>$v</td>";
			}
			$html .= "</tr>\n";
		}
		$html .= "</tbody>";
	}
	#
	# Append footer (copy of header) if required:
	if ($opts["ftr"]) $html .= $hdr; 

	$n = $nf; 
	if ($opts["ronly"] === false) $n++;
	if ($opts["ln"]) $n++;
	$n -=2;
	$html .= "<tfoot><tr class='nav'><td class='navpad'></td><td class='hdr' colspan='$n'><div class='navigation'>";

	if ($nr > 0) {
		#
		# Add navigation bar:
		if ($so > 0) {
			$d = glist_dopts($opts, 0, $pl);
			$html .= "<a onclick='glist_go(\"$id\", $pdat, $d)'><img height='25px' src='images/start.norm.png' onmouseover='this.src=\"images/start.pre.png\"' onmouseout='this.src=\"images/start.norm.png\"'/></a>";
			$d = glist_dopts($opts, $po, $pl);
			$html .= "<a onclick='glist_go(\"$id\", $pdat, $d)'><img height='25px' src='images/sarrow.left.norm.png' onmouseover='this.src=\"images/sarrow.left.pre.png\"' onmouseout='this.src=\"images/sarrow.left.norm.png\"'/></a>";
		} else {
			$html .= "<img height='25px' src='images/start.dis.png'/>";
			$html .= "<img height='25px' src='images/sarrow.left.dis.png'/>";
		}

		$d = glist_dopts($opts, $so, $pl);
		$html .= "<input type='text' style='width: 15px; text-align: right;' value='$cp' id='glist_inp_$id' onchange='glist_go(\"$id\", $pdat, $d, (this.value - 1) * $pl, $pl)'/> on $np";

		if ($no <= $lo) {
			$d = glist_dopts($opts, $no, $pl);
			$html .= "<a onclick='glist_go(\"$id\", $pdat, $d)'><img height='25px' src='images/sarrow.right.norm.png' onmouseover='this.src=\"images/sarrow.right.pre.png\"' onmouseout='this.src=\"images/sarrow.right.norm.png\"'/></a>";
			$d = glist_dopts($opts, $lo, $pl);
			$html .= "<a onclick='glist_go(\"$id\", $pdat, $d)'><img height='25px' src='images/end.norm.png' onmouseover='this.src=\"images/end.pre.png\"' onmouseout='this.src=\"images/end.norm.png\"'/></a>";
		} else {
			$html .= "<img height='25px' src='images/sarrow.right.dis.png'/>";
			$html .= "<img height='25px' src='images/end.dis.png'/>";
		}
	}
	$d = glist_dopts($opts, $so, $pl);
	$html .= "</td><td class='navpad'>lignes par page:<input style='width: 20px; text-align: right;' id='lpp' value='$pl' onchange='glist_go(\"$id\", $pdat, $d, null, this.value)'></tr></tfoot></table>";

	return $html;
}

function glist_ctrl() {
	$a = new args();

	$prov = $a->val("prov");
	$opts = $a->val("opts");

	if ($a->has("save_opts") && $a->val("save_opts") == true) glist_user_pref_save($prov, $opts);

	return glist(new prov($prov), $opts);
}

?>
