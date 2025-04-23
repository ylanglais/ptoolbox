<?php

require_once("lib/dbg_tools.php");
require_once("lib/prov.php");

$p = new prov("db", "default.param.folder");
print("Folder.name: " . json_encode($p->fdata("name")) . "\n");
print("Folder.name: " . json_encode($p->fdata("name", "T")) . "\n");
print("Folder.name: " . json_encode($p->fdata("name", "Ta")) . "\n");

$p = new prov("view", "Droits");
print("Droits.Role: " . json_encode($p->fdata("Role")) . "\n");
print("Droits.Link: " . json_encode($p->fdata("Datalink")) . "\n");
print("Droits.Link: " . json_encode($p->fdata("Datalink", "default.") ). "\n");
print("Droits.Link: " . json_encode($p->fdata("Datalink", "default.p")) . "\n");

$p = new prov("db", "default.param.folder");
print("Folder.id: " . json_encode($p->fdata("id")) . "\n");
print("Folder.id: " . json_encode($p->fdata("id", "1")) . "\n");
print("Folder.id: " . json_encode($p->fdata("id", "10")) . "\n");
print("Folder.id: " . json_encode($p->fdata("id", "100")) . "\n");

$p = new prov("db", "rt4.Tickets");
print("rt4.Tickets: " . json_encode($p->fdata("id", "2")) . "\n");
print("rt4.Tickets: " . json_encode($p->fdata("id", "20")) . "\n");
print("rt4.Tickets: " . json_encode($p->fdata("id", "200")) . "\n");
