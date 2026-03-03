<!DOCTYPE html>
<link rel="stylesheet" type="text/css" href="style.php">
<script src='js/jspp.js'></script>
<?php

require_once("lib/jspp.php");

$d = file_get_contents("tjspp.json");
#print($d);
#print("\n<br/>\n");
$d = json_decode($d);
print(jspp($d));
