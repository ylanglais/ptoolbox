<?php
require_once("wsd/veeam.php");
require_once("lib/dbg_tools.php");
require_once("lib/prov.php");
require_once("lib/util.php");

$v = new veeam(); 

#dbg($v->vmjobs(0,2));


$list = [ 
	"vbrJobs/vmBackupJobs"    => "vm", 
	"vbrJobs/fileToTapeJobs"  => "to tape",
	"vbrJobs/fileBackupJobs"  => "cifs",
	"vbrJobs/AgentBackupJobs" => "agent",
];

$p = new prov("db", "default.infra.vjob");
$cnt = 0;
foreach ($list as $jtype => $type) {
	print("$jtype:\n");
	$d = $v->page_get($jtype);


	$flds = [ "name", "backupServerId", "description", "status", "lastRun", "avgDurationSec", "lastRunDurationSec", "lastTransferredDataBytes" ];
	$ids  = [ "vmBackupJobUid" => "vm", "fileBackupJobUid" => "cifs", "agentBackupJobUid" => "agent", "fileToTapeJobUid" => "to tape"   ];
	foreach ($d->items as $i) {
		$cnt++;
		$a = [];	
		$i->lastRun[10] = " ";
		$i->lastRun = substr($i->lastRun, 0, -1);
		dbg("lastRun = $i->lastRun"); 
		foreach ($flds as $f) {
			$a[strtolower($f)] = esc($i->$f);
			foreach ($ids as $idn => $idt) {
				if (property_exists($i, $idn)) {
					$a["jid"]   = $i->$idn;
					$a["jtype"] = $idt;
					break;
				}
			}
		}
		print("$cnt ----------------------------------------------\n");
		#dbg($a);
		$p->put($a);
		print("-------------------------------------------------\n");
	}
	
}





