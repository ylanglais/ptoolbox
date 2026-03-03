<?php
require_once("usr/lib/quidam.php");
$qid = new quidam("quidprod");
$id = $qid->mail2id("ylanglais@gmail.com");
print("ylanglais@gmail.com --> $id\n");

$d = $qid->user_data($id);
$i =  0;
foreach ($d as $o) {
	print("$i:\n");
	foreach ($o as $k => $v) {
		print("\t$k --> $v\n");
	}
	$i++;
}
	
