<?php
require_once("lib/date_util.php");

$dates = [
	"2024/02/25",
	"2024-02-25",
	"20240225",
	"25022024",
	"25/02/2024",
	"25-02-2024"
];

foreach ($dates as $d) {
	echo "$d to human: " . date_to_human($d) . "\n";
	echo "$d to db   : " . date_to_db($d)    . "\n";
}
?>
