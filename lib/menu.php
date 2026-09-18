<?php

require_once("lib/session.php");
require_once("lib/args.php");

function menu_ctrl() {
	#session::enforce();
	$s = new session();
	$a = new args();

	if ($a->has("reload")) {
		require_once("parts/menu.php");
		print(menu_content());
		return;
	}
	
	if (!$a->has("menu_cur")) {
		err("no menu_cur");
		return "";
	}
	$menu_cur = $a->val("menu_cur");
	$s->pushvar("menu_cur", $menu_cur);
	if ($a->has("entry_cur")) {
		$entry_cur = $a->val("entry_cur");
		$s->pushvar("entry_cur", $entry_cur);
	}
	if ($a->has("data_cur")) {
		$data_cur = $a->val("data_cur");
		$s->pushvar("data_cur", $data_cur);
	}
	if ($a->has("user_data")) { 
		$user_data = $a->val("user_data");
		$s->pushvar("user_data", $user_data);
	}
	return "";
}
?>
