<?php
require_once("lib/args.php");
$a = new args();
$p = $a->val("params");
print("<table><tr><th>key</th><th>value</th></tr>\n");
foreach( $p as $k => $v) {
	print("<tr><td class='hdr'>$k</td><td>$v</td></tr>\n");
}
print("</table>");
