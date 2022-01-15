<?php

require_once("lib/style.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");

foreach (glob("images/*white*.png") as $file) {
	$image = new Imagick($file);
	foreach ([ 'norm' => 'image_normal', 'pre' => 'image_pre', 'sel' => 'image_selected', 'dis' => 'image_disabled' ] as $k => $c) {
		$i = $image->clone();
		dbg("target for $k is " . style::value($c));
		$i->opaquePaintImage("white", style::value($c), 0, false);
		$name = str_replace("white", $k, $file);
		dbg("$file -> $name");
		$i->writeImage($name);
	}	
}

