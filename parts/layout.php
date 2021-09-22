<?php

function layout_part($divid, $divclass, $part) {
	$str = "";
	$fi  = "parts/$part.php";
	$fn  = $part. "_content";
	if (!file_exists($fi)) return $str;
	include($fi);
	$str .= "<div id='$divid' class='$divclass'>";
	if (function_exists($fn)) $str .= $fn();
	$str .= "</div>\n";
	return $str;
}

print("<div id='body'>\n");
print(layout_part("heading", "heading", "heading"));
print(layout_part("menu", "menu", "menu"));
print("<div id='data_area'></div>\n</div>\n");
