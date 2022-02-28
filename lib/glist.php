<?php

require_once("lib/args.php");
require_once("lib/util.php");
require_once("lib/prov.php");
require_once("lib/locl.php");
require_once("lib/dbg_tools.php");

function glist_dopts($dopts, $offset= false, $page = false) {
	$d = $dopts;
	if ($offset !== false) $d["start"] = $offset;
	if ($offset !== false) $d["page"]  = $page;

	return json_encode($d);
}

function glist($prov, $opts = []) {
	#
	# Prepare options:
	$dopts = [ "start" => 0, "page" => 25, "ln" => true, "hdr" => true, "ftr" => true, "sort" => false, "order" => "up", "id" => false , "gform_id" => false ];
	if (is_array($opts)) {
		foreach ($dopts as $k => $v) if (!array_key_exists($k, $opts)) $opts[$k] = $v;
	} else $opts = $dopts;
	
	# 
	# Gen identifier if not present:
	if ($opts["id"] === false) $opts["id"]  = "glist_" . gen_elid();		
	$id = $opts["id"];

	#
	# Prepare table:
	$html = "<table class='glist' id='$id'>\n";

	#
	# Check if provider is set/data present:
	if (!is_object($prov))  return $html . "<tr><td> No data </td></tr></table>\n";

	#
	# Get fields:
	$fields = $prov->fields();
	$nf     = count($fields);

	$pdat = $prov->data();
	$keys = $prov->keys();
	#
	# Compute header / footer only if required:
	if ($opts["hdr"] || $opts["ftr"]) { 
		$hdr = "<tr>";
		
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
	# add header if required:
	if ($opts["hdr"])  $html .= $hdr;

	#
	# Add localization support:
	$locl = new locl();

	#
	# Get data from provider from start offset + nb lines per page:
	$all = $prov->query($opts["start"], $opts["page"], $opts["sort"], $opts["order"]);
	if (!is_array($all) || (count($all) == 0)) {
		#
		# No data present: 
		$n = $nf;
		if ($opts["ln"]) $n++;
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
		$html .= "<tbody>";

		#
		# Print all result from start offset (so) + page count (pl):
		foreach ($all as $o) {
			$i++;

			$qry = [];

			if ($opts["gform_id"] !== false) {
				foreach ($keys as $k) {
					if (!property_exists($o, $k)) {
						if (!property_exists($o, "_hidden_$k")) {
							$qry = [];
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
				$vdata = '{ "prov": '. $pdat . ', "req": '. json_encode($qry) . ', }';
				$html .= " onclick='glist_view(\"".$opts["gform_id"]."\", $vdata)'";
			} 
			$html .=">";
 	
			if ($opts["ln"]) $html .= "<td class='num'>".($i)."</td>";
			
			foreach ($fields as $k) {
				$cl = "";
				if (property_exists($o, $k)) { 
					$v = $locl->format($o->$k);
					if (is_float($v) || is_numeric($v) || substr($v, -1) == "%") $cl = "class='number'";
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
	## if ($opts["ftr"]) $html .= $hdr; 

	$n = $nf; if ($opts["ln"]) $n++;
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
	#dbg("in glist_ctrl");
	$a = new args();

	$prov = $a->val("prov");
	$opts = $a->val("opts");

	#dbg(json_encode($prov));
	#dbg(json_encode($opts));

	return glist(new prov($prov), $opts);
}

?>
