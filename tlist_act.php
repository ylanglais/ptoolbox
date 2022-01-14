<?php

require_once("lib/args.php");
require_once("lib/tlist.php");
require_once("lib/dbg_tools.php");

$a = new args();

$table     = $a->val("table");
$fieldlist = $a->val("fieldlist");
$start     = $a->val("start");
$page      = $a->val("page");
$where     = $a->val("where");
$edit      = $a->val("edit");

if ($fieldlist == "" || $fieldlist === false) $fieldlist = null;
print(tlist($table, $fieldlist, $start, $page, $where, $edit));