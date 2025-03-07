<?php
require_once("lib/date_util.php");
class ping {
	function mping() {	
		$a= (object) [];
		$a->ping  = dbstamp();
		return $a;
	}
}
