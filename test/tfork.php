<?php
require_once("lib/dbg_tools.php");

function fork($duration) {
	$pid = pcntl_fork();
	if ($pid < 0) {
		err("cannot fork");
	} else if ($pid == 0) {
		child($duration);	
	}
}
function child($duration) {
	dbg("C$duration: $pid (".getmypid().")");
	dbg("C$duration: sleep $duration");
    sleep($duration);
	dbg("C$duration: exiting");
	exit; 
}

fork(3);
fork(4);
dbg("P: $id (".getmypid().")");
dbg("P: sleep 2");
sleep(2);
dbg("P: wait children");
dbg("P: children exited (?)");
pcntl_wait($status); //Protect against Zombie children
dbg("P: exiting");
exit;

?>
