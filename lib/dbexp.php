<?php

require_once("lib/dbg_tools.php");
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/ora.php");
require_once("lib/prov.php");
require_once("lib/glist.php");

function dbexp() {
	$q = new query("select dbs from tech.dbs order by dbs");
	print("<div class='dbexpp'><div id='dbexp_dbs' class='dbexp'><table class='glist'><tr><th>dbs</th></tr>\n");	
	print("<tr><td onclick='dbexp_tables(this, \"default\")' onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")'>default</td></tr>\n");

	while ($o = $q->obj()) {
		print("<tr><td onclick='dbexp_tables(this, \"$o->dbs\")' onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")'>$o->dbs</td></tr>\n");
	}
	print("</table></div>");
	print("<div id='dbexp_tables' class='dbexp'></div>\n");	
	print("<div id='dbexp_data' class='dbexp'></div></div>\n");	
}

function dbexp_table_list($dbs) {
	$str = "<table class='glist'><tr><th>Tables</th></tr>\n";
	$drv = "pgsql";
	if ($dbs != "default") {
		$q = new query("select * from tech.dbs where dbs = '$dbs'");
		if ($q->nrows() < 1) return "";
		$o = $q->obj();
		$drv = $o->drv;
	}
	if ($drv == "odbc") {
		$ora = new db($dbs);
		$l = [];
		$q = new query($ora, "select distinct owner as schema FROM all_tables");
		while ($o = $q->obj()) { 
			$s = $o->SCHEMA;
			$q2 = new query($ora, "SELECT TABLE_NAME as table_name from all_tables where Owner = '$s'");
			while($o2 = $q2->obj()){
				array_push($l, "$s." . $o2->TABLE_NAME);
			}
		}	
	} else {
		$db = new db($dbs);
		$s = $db->schemas();
		if ($s == []) {
			$l = $db->tables();
		} else {
			$l = [];
			foreach ($s as $ss) {
				$sl = $db->tables($ss);
				$l  = array_merge($l , $sl);
			} 
		}
	}
	foreach ($l as $t) {
		$str .= "<tr><td onclick='dbexp_data(this, \"$dbs\", \"$t\")' onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")'>$t</td></tr>\n";
	}
	$str .= "</tables>\n";	
	return $str;
}
function dbexp_data($dbs, $table) {
	#dbg("$dbs.$table");
	return glist(new prov("db", "$dbs.$table"));
}

function dbexp_ctrl() {
	$a = new args();
	if ($a->has("action")) {
		$action = $a->val("action");
		if ($action == "dbexp_table_list") {
			if (!$a->has("dbs")) return "no dbs";
			return dbexp_table_list($a->val("dbs"));
		} else if ($action == "dbexp_data") {
			if (!$a->has("dbs"))   return "no dbs";
			if (!$a->has("table")) return "no table";
			return dbexp_data($a->val("dbs"), $a->val("table"));
		} else {
			return "bad action $action";	
		}
	} 
	return "";
}
?>
