<?php

require_once("lib/query.php");
require_once("lib/prov.php");
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");

class majos {
	#const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and osfamily = 'LINUX' and env = 'test'";
	#const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and name='mongodb-1'";
	const req = "select name, distrib from infra.host where isvm and ison = true and lastping is not null and osfamily = 'LINUX' and (type is null or type != 'Appliance')";

	function host_list_csv() {
		$q = new query(self::req);
		$o = (object)[];
		$o->list = $q->csv();
		return $o;
	}
	function host_list() {
		$q = new query(self::req);
		$o = (object)[];
		$o->list = $q->all();
		return $o;
	}
	function save_all($data) {	
		if (is_string($data)) $data = json_decode($data);
		if (!is_array($data)) return;
		foreach ($data as $a) {
			if (is_object($a)) $this->save($a);
		}
	}
	function save($a) {
		if (!is_object($a)) return;
	
		$p = new prov("db", "default.infra.majos");
		$p->put($a);
	}
}

?>
