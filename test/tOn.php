<?php

function tp($i) {
	print("$i: \n");
	print("-> is_int($i)     is " . (is_int($i) ? "true" : "false") . "\n"); 
	print("-> is_string($i)  is " . (is_string($i) ? "true" : "false") . "\n"); 
	print("-> is_null($i) is " . (is_null($i) ? "true" : "false") . "\n"); 
	print("-> === null) is " . (($i === null) ? "true" : "false") . "\n"); 
	print("-> == \"null\") is " . (($i == "null") ? "true" : "false") . "\n"); 
	print("-> gettyp($i) == \"null\") is " . ((gettype($i) == "null") ? "true" : "false") . "\n"); 
	print("=> !is_int($i) && ($i === null || $i == \"null\" " . (!is_int($i) && ($i === null || $i == "null") ? "true" : "false") . "\n");
	print("=> gettype($i) == 'null' || $i === 'null' || $i === null " . ((gettype($i) == 'null' || $i === 'null' || $i === null) ? "true" : "false") . "\n");
	print("\n");
}

$l = [ 
	0,
	"",
	"null",
	'null',
	null
];

foreach ($l as $i) tp($i);




