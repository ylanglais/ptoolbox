<?php 
require_once("lib/style.php");

function heading_content() {
	$logo = style::value("logo");
 
	$str ="<table class='heading'>
		<tr class='heading' align='center' valign='middle'>
			<td class='heading logo'>
				<img class='heading' src='$logo'>
			</td><td></td>
		</tr>
	</table>";
	return $str;
}
?>

