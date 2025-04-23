
<?php
require_once("lib/dbg_tools.php");
require_once("lib/curl.php");
class vmw {
	function __construct(){ 
		$this->host  = false;
		$this->token = false;
		$this->c     = false;

		$f = "conf/vmw.php";
		if (!file_exists($f)) {
			err("no vmw config file");
			return;
		}
		include($f);
		$this->host = $vmw_host ;	
		
		$ba = "Authorization: Basic " .  base64_encode("$vmw_user:$vmw_pass");

		$this->c = new curl ($this->host, [ $ba, "Content-type: application/json" ], false, false);	
		$r = $this->c->post("session", null);
		if ($r === false) {
			$this->c = null;
			err("Bad authentification");
		}
		#info("token = '$r'");
		$r = str_replace('"', '', $r);
		$this->c->header( [ "Content-type: application/json", "vmware-api-session-id: $r" ]);
	}
	function hosts() {
		if ($this->c === false) return;
		if (($r = $this->c->get("vcenter/host")) === false) return false;
		return json_decode($r);
	}
	function host($id) {
		if($this->c === false) return;
		if (($r = $this->c->get("vcenter/vm/$id")) === false) return false;
		return json_decode($r);
	}
	function vms() {
		return $this->vm();
	}
	function vm($id = "") {
		if($this->c === false) return;
		if ($id != "") $id = "/$id";
		if (($r = $this->c->get("vcenter/vm$id")) === false) return false;
		return json_decode($r);
	}
}
?>
