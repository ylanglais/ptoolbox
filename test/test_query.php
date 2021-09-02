<?php

require_once("lib/db.php");
require_once("lib/query.php");

print("default - select login from tech.user:\n");
$q = new query("select login from tech.user");
while ($o = $q->obj()) {
	print("login = $o->login\n");
}
print("\ndefault - select login from tech.user where login = :login:\n");
$q = new query("select login from tech.user where login = :login", [":login" => "ylanglais" ]);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

print("\ndefault - select login from tech.user where login = :login - reverse parmeter order:\n");
$q = new query([":login" => "ylanglais" ], "select login from tech.user where login = :login");
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

$db = new db("dbs=m_local_pfwk");
print("\nm_local_pfwk - select login from users:\n");
$q = new query($db, "select login from users");
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

$db = new db("dbs=m_local_pfwk");
print("\nm_local_pfwk - select login from users - reverse parameter order:\n");
$q = new query($db, "select login from users");
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

print("\nm_local_pfwk - select login tech.users where login = :login: - db, sql, data\n");
$q = new query($db, "select login from users where login = :login", [":login" => "admin"]);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

print("\nm_local_pfwk - select login tech.users where login = :login: - db, data, sql\n");
$q = new query($db, [":login" => "admin"], "select login from users where login = :login");
while ($o = $q->obj()) {
	print("login = $o->login\n");
}


print("\nm_local_pfwk - select login tech.users where login = :login: - sql, db, data\n");
$q = new query("select login from users where login = :login", $db, [":login" => "admin"]);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

print("\nm_local_pfwk - select login tech.users where login = :login: - sql, data, db\n");
$q = new query("select login from users where login = :login", [":login" => "admin"] , $db);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}


print("\nm_local_pfwk - select login tech.users where login = :login: - data, sql, db\n");
$q = new query([":login" => "admin"], "select login from users where login = :login",  $db);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}

print("\nm_local_pfwk - select login tech.users where login = :login: - data, db, sql\n");
$q = new query([":login" => "admin"], $db, "select login from users where login = :login", [":login" => "admin"]);
while ($o = $q->obj()) {
	print("login = $o->login\n");
}




