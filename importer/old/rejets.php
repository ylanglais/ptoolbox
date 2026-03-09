<?php 
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/util.php");

class rejets {
	private $_odb = false;

	function __construct() {
		if (!file_exists("Configs/logimport.conf")) {
		_err("no Configs/logimport.conf file");
		return;
	}
		include("Configs/logimport.conf");
		$this->_odb = new db($logimport_dsn, $logimport_user, $logimport_pass);
		if (($e = $this->_odb->error()) !== false) {
			_err("cannot connect to db");
			return;
		}	
	}

	function log($id_flux, $flux, $fichier, $ref, $raison) {
		$set = [];
		foreach (["id_flux", "flux", "fichier", "ref", "raison"] as $k) {
			if ($$k  == "" || $$k == null) {
				array_push($set, "null");
			} else {
				array_push($set, "'".esc($$k)."'");
			}
		}
		$sql = "insert into tech.rejets (id_flux, flux, fichier, reference, raison) values (".implode(",", $set).")";
		$q = new query($sql, $this->_odb);
		if (($r = $q->error()) !== false) {
			_err($r);
		}
	}
}
