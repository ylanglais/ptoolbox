<?php

require_once("lib/dbg_tools.php");
require_once("lib/query.php");

$p = new query("select * from tech.purge");
while ($o = $p->obj()) {
	if ($o->arch_stable != "" && $o->arch_stable != null) {
		$q = new query("SELECT to_regclass($o->arch_stable)");
		if ($q->nrows() != 1) new query("create table $o->arch_stable (like $o->stable_name)");
		$q = new query("insert into $o->arch_stable select * from $o->stable_name where $o->column_name < (now() - interval '$o->rentention')");
		if (($e = $q->err()) != false) {
			err("cannot archive $o->stable_name");
			continue;
		}
	}
	new query("delete from $o->stable_name where $o->column_name < now() - interval '$o->rentention'"); 
}

