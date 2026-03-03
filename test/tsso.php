<?php
#require_once("lib/dbg_tools.php");
$a =  apache_request_headers();
print(json_encode($a));
print("\n<br/>\n");
print(json_encode($_SERVER) ."\n");
