<?php

$a = [];

function t($v) {
	global $a;
	if (array_key_exists($v, $a)) {
		return $a[$v];
	}
	return false;
}

$a[0]  = (object) [];
$a[0]->a = "a";
$a[0]->b = "b";


$l    = t(0);
$l->b = "c";

print_r($a);

