<?php

require_once("lib/dbg_tools.php");

function f1() {
	_step();
	foreach (["dbg", "info", "warn", "err"] as $f) {
		print(">> call $f('test_$f'): \n"); 
		$f("test_$f");
		print(">> call _$f('test__$f'): \n"); 
		$f("test__$f");
	}	
	f2();
	_step();	
}
function f2() {
	_step();
	foreach (["dbg", "info", "warn", "err"] as $f) {
		print(">> call $f('test_$f'): \n"); 
		$f("test_$f");
		print(">> call _$f('test__$f'): \n"); 
		$f("test__$f");
	}	
	_step();
}

_step();
foreach( [ "html_err", "html_warn", "html_info", "html_dbg" ] as $f) {
	print(">> call $f('test_$f'): \n"); 
	$f("test_$f");
}

$a = json_decode('{"a": "value of a", "b": "value of b", "c": ["c1", "c2", "c3"]}'); 
print(">> call html_array('test_html_array', ". json_encode($a) ."): \n"); 
html_array('test_html_array', $a);
_step();
f1();
_step();
