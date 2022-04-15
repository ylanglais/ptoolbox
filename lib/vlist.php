<?php

require_once("lib/locl.php");

function vlist(array $table, $index = false, $url = false, $show_index = false, $start = 1, $repeat_hdr = true) {
	$loc = new locl();
	$hdr = $table[0];
	$str = "<table class='glist'>";
	$hst = "<tr><th><i>#</i></th>";
	foreach ($hdr as $f => $v) {
		if ($show_index === false && $index !== false && $index == $f) continue;
		$hst .= "<th>$f</th>";
	}
	$hst .= "</tr>\n";

	$str .= $hst;

	$i = $start - 1;
	foreach ($table as $row) {
		$i++;
		$str .= "<tr onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")'";
		if ($index !== false && $url != false) 
			$str .= " onclick='vlist_call(\"$url\", \"".$row[$index]."\")'";
		$str .= "><td class='hdr num'>$i</td>";
		foreach ($row as $f => $v) {
			if ($show_index === false && $index !== false && $index == $f) continue;
			$str .= "<td";
			if (is_numeric($v) || substr($v, -1) == "%")
				$str .= " class='number'";
			$str .= ">" . $loc->format($v)	. "</td>";
		}
		$str .= "</tr>\n";
	}

	if ($repeat_hdr === true) 
		$str .= $hst;
	
	$str .= "</table>";

	return $str;
}

/*
function page($dprov, $flds, $start, $page = 25) {
	$out = "<table><tr><td>";
############### TOTO :
	#ERROR:	
	$out .= vlist(array $table, $index = false, $url = false, $show_index = false, $start = 1, $repeat_hdr = true);
	$out .= "</tr>";
	$out .= "<tr><td class='hdr' colspan='".($nfields + 1)."'><div class='navigation'>";
	if ($start == 0) 
		$out .= "<a onclick='glist_go(\"$id\", \"$table\", \"$fieldlist\", $fir_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/start.norm.png'/></a>";
	else
		$out .= "<a onclick='glist_go(\"$id\", \"$table\", \"$fieldlist\", $pre_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/sarrow.left.norm.png'/></a>";

	$out .= "<input type='text' style='width: 15px; text-align: right;' value='$sp' id='".$table. "_".$page."'onchange='glist_go(\"$id\", \"$table\", \"$fieldlist\", (this.value - 1) * $page, \"$page\", \"$where\", \"$edit\")'/> on $np";

	if ($nex_off <= $las_off)
		$out .= "<a onclick='glist_go(\"$id\", \"$table\", \"$fieldlist\", $nex_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/sarrow.right.norm.png'/></a>";

	if ($start < $las_off)
		$out .= "<a onclick='glist_go(\"$id\", \"$table\", \"$fieldlist\", $las_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/end.norm.png'/></a>";

	$out .= "</div></td></tr></table>";
	return $out;
}

class dataprov {
	function __construct($data, $page_size = 25) {
	}

	function page_count() {
	}
	function page_n(int $page) {
	}
	function current_start() {
	}
}
*/
