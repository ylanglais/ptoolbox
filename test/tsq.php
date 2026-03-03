<?php
require_once("lib/dbg_tools.php");
require_once("lib/query.php");

$qrys = [
	# "insert into ttt values (:id, :name, :tof)" => [ ":id" => 42, ":name" => "l'anglais", ":tof" => "true"], 
	"select * from ttt where name = :login" => [ ":login" => "toto" ],
	"select * from ttt where id   = :login" => [ ":login" =>    1 ],
	"select * from ttt where tof =  :login" => [ ":login" =>  'f' ],
	"select * from ttt where tof  = :login" => [ ":login" =>  true ],
	"select * from ttt where id  =  :login" => [ ":login" =>    2 ],
];

foreach ($qrys as $q => $val ) { 
	dbg("---> $q <--- with " . json_encode ($val));
	$q = new query($q,  $val);
	while ($o = $q->obj()) dbg($o);
}
