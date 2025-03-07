<?php

require_once("lib/query.php");
require_once("lib/prov.php");
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");

class hostdata {
	#const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and osfamily = 'LINUX' and env in ( 'test', 'dev', 'rec' ) and (type is null or type != 'Appliance')";
	#const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and name='mongodb-1'";
	const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and osfamily = 'LINUX' and (type is null or type != 'Appliance')";
	function host_list() {
		$q = new query(self::req);
		$o = (object)[];
		$o->list = $q->all();
		return $o;
	}
	function host_all_put($data) {	
		if (is_string($data)) $data = json_decode($data);
		if (!is_array($data)) return;
		foreach ($data as $a) {
			if (is_object($a)) $this->save($a);
		}
	}
	function host_put($a) {
		if (is_string($a)) $a = json_decode($a);
		if (!is_object($a)) return;
	
		$p = new prov("db", "default.infra.host");
		$p->put($a);
	}
}


?>
