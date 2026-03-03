<?php
require_once("lib/curl.php");
$user = 'admin';
$pass = 'xxxxx';
$url  = 'https://barracuda.lfm.lan/restapi/v3.2';


$c = new curl($url, [ "Content-type: application/json" ]);

$r = $c->post("login", '{"username": "'. "$user". '", "password": "' . $pass .'"}');
print("$r = " . print_r($r, true) . "\n");
?>
