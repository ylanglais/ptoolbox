<?php
require_once("lib/apic.php");

$url = "http://lfmtdbrec/api.php";
$usr = 'api';
$pwd = 'rIRNsZ75eY5Is';
$dbg = false;

print("connecting to $url\n");
$a = new apic([ "url" => $url, "login" => $usr, "passwd" => $pwd, "debug" => $dbg ]);

/**
print("test():\n");
if (($r = $a->test()) === false)
    print("test KO  ". json_encode($r) ."\n");
else
    print("test ok\n");
**/
print("ping():\n");
if (($r = $a->ping()) !== false)
    print("pinged at $r\n");

print("call ping\n");
if (($r = $a->call("test", "ping")) !== false)
    print("srv timestamp is " .$r->tstamp . "\n");

print("call test/add w/ a = 2, b = 4\n");
if (($r = $a->call("test", "add", json_encode([ "a" => 2, "b" => 4 ]))) !== false)
    print(json_encode($r)."\n");

print("call test/add w/ a = 2.1, b = 4\n");
if (($r = $a->call("test", "add", json_encode([ "a" => 2.1,    "b" => 4 ]))) !== false)
    print(json_encode($r)."\n");

print("call test/add w/ a = \"toto\", b = 4\n");
if (($r = $a->call("test", "add", json_encode([ "a" => "toto", "b" => 4 ]))) !== false)
    print(json_encode($r)."\n");

print("call test/add w/  b = 4\n");
if (($r = $a->call("test", "add", json_encode([ "b" => 2 ]))) !== false)
    print(json_encode($r)."\n");

#c->debug(true);
print("call test/count w/ a = [\"a\", \"b\", \"c\"]\n");
if (($r = $a->call("test", "count", '{"a": [ "a", "b", "c"]}')) !== false)
    print(json_encode($r)."\n");
