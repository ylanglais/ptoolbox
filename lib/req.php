<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/scrm.php");
require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/session.php");

function req_ctrl() {
	session::enforce();
	$a = new args();
	if (!$a->has("token")) {
		err("No token");
		return;
	}
	$data = scrm_un($a->val("token"));
	if (!propery_exists($data, "qry")) {
		err("no request");
		return;
	}
	if (property_exists($data, "dbs")) $req->dbs = $data->dbs;
	else                               $req->dbs = "default";

	$req->db = new db($req->dbs);
	$q = new query($req->db, $req->qry);
	return json_encode($q->all());
}

?>
