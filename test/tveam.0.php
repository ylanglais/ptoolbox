<?php
require_once("wsd/veeam.php");
require_once("lib/dbg_tools.php");

$v = new veeam(); 

#dbg($v->vmjobs(0,2));


$list = [ 
	"vbrJobs/vmBackupJobs", 
	"vbrJobs/CloudDirectorBackupJobs", 
	"vbrJobs/backupToTapeJobs", 
	"vbrJobs/backupCopyJobs", 
	"vbrJobs/sureBackupJobs",
	"vbrJobs/sureBackupJobs",
	"vbrJobs/vmCopyJobs",
	"vbrJobs/vmSnapshotOnlyJobs",
	"vbrJobs/cdpPolicies",
	"vbrJobs/fileToTapeJobs",
	"vbrJobs/objectToTapeJobs",
	"vbrJobs/fileCopyJobs",
	"vbrJobs/fileBackupJobs",
	"vbrJobs/AgentBackupJobs",
	"vbrJobs/AgentPolicies",
	"vbrJobs/applicationBackupJobs",
	"vbrJobs/applicationBackupJobs/logJobs",
	"vbrJobs/transactionLogBackupJobs",
	"vbrJobs/objectStorageBackupJobs",
	
];
foreach ($list as $i) {
	print("$i:\n");
	dbg($v->page_get($i));
	print("\n");
}


dbg($v->data_get("vbrJobs/vmBackupJobs/6ad10c9d-62b8-478f-a28e-ca280fa07ac0")); 



