<?php

session_start();

print("<tt>" . json_encode($_SESSION) . "\n</tt>");

if (!array_key_exists("TEST", $_SESSION)) {
	print("<tt>\$_SESSION[\"TEST\"] is unset, set it to 1\n</tt>\n");
	$_SESSION["TEST"] = 1;
} else {
	print("<tt>\$_SESSION[\"TEST\"] is ".$_SESSION["TEST"].", add one to it\n</tt>\n");
	$_SESSION["TEST"]++;
}
