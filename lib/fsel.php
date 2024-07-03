<?php
require_once('lib/prov.php');
require_once('lib/util.php');

function fsel($ctrl, $data, $all, $sel = [], $popupid) {
	if (is_string($all)) $all = json_decode($all);
	if (is_string($sel)) $sel = json_decode($sel);

	if ($sel === false || $sel == []) $sel = $all;

	$ava = [];
	foreach ($all as $f) if (!in_array($f, $sel)) array_push($ava, $f);

	$nb = count($all);

	$fid = gen_elid();

	$str  = "<div id='fsel_$fid' class='fsel' align='center'>\n<input type='hidden' id='fsel_ctrl_$fid' value='$ctrl'><input type='hidden' id='fsel_data_$fid' value='$data'>"
	      . "<table id='fsel_table'><tr><th colspan='2'>Champs sélectionnés</th><th></th><th>Champs disponibles</th></tr><tr>"
	      . "<td>"
		  . "<a onclick='fsel_up()'><img src='images/arrow.up.norm.png'   onmouseover='this.src=\"images/arrow.up.pre.png\"'   onmouseout='this.src=\"images/arrow.up.norm.png\"'   height='20px' width='20px'></a><br/>"
		  .	"<a onclick='fsel_dw()'><img src='images/arrow.down.norm.png' onmouseover='this.src=\"images/arrow.down.pre.png\"' onmouseout='this.src=\"images/arrow.down.norm.png\"' height='20px' width='20px'></a><br/>"
		  . "</td>"
	      . "<td><select multiple id='fsel_sel' size='$nb' style='height:auto;width:100px;'>";
	foreach ($sel as $f) {
		$str .= "<option value=\"$f\">$f</option>\n";
	}
	$str .= "</select></td><td>"
	      . "<a onclick='fsel_rem()'><img src='images/arrow.right.norm.png' onmouseover='this.src=\"images/arrow.right.pre.png\"' onmouseout='this.src=\"images/arrow.right.norm.png\"' height='20px' width='20px'></a><br/>" 
	      . "<a onclick='fsel_add()'><img src='images/arrow.left.norm.png'  onmouseover='this.src=\"images/arrow.left.pre.png\"'  onmouseout='this.src=\"images/arrow.left.norm.png\"'  height='20px' width='20px'></a><br/>" 
		  . "</td>"
	 	  . "<td><select multiple id='fsel_ava' size='$nb' style='height:auto;width:100px;'>";

	foreach ($ava as $f) {
		$str .= "<option value=\"$f\">$f</option>\n";
	}
	$str .= "</select></td>"
		  . "</tr><tr><td colspan='3'>"
		  . "<button onclick='fsel_apply(\"$popupid\")'>Appliquer</button><button onclick='fsel_cancel(\"$popupid\")'>Annuler</button>"
		  . "</td></tr></table></div></div>";

	return $str;
}

?>
