<?php 
require_once("lib/style.php");
require_once("lib/query.php");

function heading_content() {
	$logo = style::value("logo");
	$env = $ver = "";

	$q = new query("select * from param.enver");
	if ($o = $q->obj()) {
		$env =  $o->env;
		$ver = $o->ver . "." . $o->rev;
	}
 
	$str ="<table class='heading'>
		<tr class='heading' align='center' valign='middle'>
			<td class='heading logo'>
				<img class='heading' src='$logo'>
			</td><td>
			</td>
			<td class='enver'>$env $ver</td>
		</tr>
	</table>";
	return $str;
}
?>

