<?php
require_once("lib/db.php");

####
print("DEFaULT DB:select * from tech.dbs --> nrows\n");
$db = new db();
$db->query("select * from tech.dbs");
print ("found " . $db->nrows() . " row(s)\n");

####
print("\ndbs=default: select * from tech.user\n");
$de = new db("dbs=default");
$de->query("select * from tech.user");
print ("found " . $db->nrows() . " row(s)\n");

####
print("\ndbs=m_local_pfwk -> select * from users:\n");
$dd = new db("dbs=m_local_pfwk");
$dd->query("select * from users");
print ("found " . $db->nrows() . " row(s)\n");

####
print("\nm_local_pfwk -> select * from users:\n");
$dd = new db("m_local_pfwk");
$dd->query("select * from users");
print ("found " . $db->nrows() . " row(s)\n");

####

print("driver = " . $dd->driver() . "\n");
print("dbname = " . $dd->db_name() . "\n");
print("----\n");
####

print("-----------\n");	
print("databses from db:\n");
$t = $db->databases() ;
foreach ($t as $c) {
	print("$c\n");
}
print("----\n");
print("databses from dd:\n");
$t = $dd->databases() ;
foreach ($t as $c) {
	print("$c\n");
}
print("-----------\n");	

print("schemas for db:\n");
$t = $db->schemas() ;
foreach ($t as $c) {
	print("$c\n");
}
print("----\n");

print("schemas for dd:\n");
$t = $dd->schemas() ;
foreach ($t as $c) {
	print("$c\n");
}
print("----\n");
print("tables from db.tech:\n") ;
$t = $db->tables("tech") ;
foreach ($t as $k => $v) {
	print("$k --> $v\n");
}
print("----\n");


print("tables from dd:\n") ;
$t = $dd->tables() ;
foreach ($t as $k => $v) {
	print("$k --> $v\n");
}
print("----\n");


print("columns from m_local_pfwk". $dd->db_name . ".users: \n");
$t = $dd->table_columns("users");
foreach ($t as $c) {
	foreach ($c as $k => $v) {
		print("$k --> $v\n");
	}
	print("----\n");
}

