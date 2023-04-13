<?php
require_once("lib/dbg_tools.php");
require_once("lib/session.php");
require_once("lib/query.php");

function menu_esc($str) {
	return str_replace("'", "\\'", $str);
}
function menu_content() {
	global $_session_;
	if (!isset($_session_)) $_session_ = new session();
	# Make sure we are connected:
	$_session_->check();

	$usr   = $_session_->user->login();
	$roles = $_session_->user->roles();
	$rstr = "";

	foreach ($roles as $r) {
		if ($rstr != "") {
			$rstr .= ", ";
		}
		$rstr .= "'$r'";
	}

	if ($rstr == "") $rstr = "'user'";

	function menu_tdb_table($label, $dlink) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a class=\"menuentry\" target=\"_blank\" onclick=\"tdb_table('$l', '$dlink');menu_onclick(this);\">$label</a></li>";
	}
	function menu_new_page($label, $url) {
		return "<li class=\"menuentry\"><a class=\"menuentry\" target=\"_blank\" href='$url'>$label</a></li>";
	}
	function menu_tdb_page($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a class=\"menuentry\" onclick=\"tdb_page('$url', '$label');menu_onclick(this);\">$label</a></li>";
	}
	function menu_tdb_hdr($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a class=\"menuentry\" onclick=\"tdb_hdr('$url', '$l');menu_onclick(this);\">$label</a></li>";
	}
	function menu_tdb_rpt($label, $rptname) {
		$rn = menu_esc($rptname);
		return "<li class=\"menuentry\"><a class=\"menuentry\" onclick=\"tdb_rpt('$rn');menu_onclick(this);\">$label</a></li>";
	}
	function menu_tdb_form($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a class=\"menuentry\" onclick=\"tdb_form('$url', '$l');menu_onclick(this);\">$label</a></li>";
	}
	$str = '<form id="menusubmit"  method="post" action="">
		<input type="hidden" name="page" value="logout"/>
		</form>
		<ul id="menuul" class="menu">';

	$q = new query("select f.name as folder ,p.name as page ,p.ptype ,p.datalink ,p.pagefile ,fp.perm_name as perm from param.folder f ,param.folder_page pf ,param.folder_perm fp ,param.page p ,tech.role r where f.id = pf.folder_id and p.id = pf.page_id and fp.folder_id = f.id and r.id = fp.role_id and r.name in ($rstr) order by f.id, pf.page_order");

	$fold = "";

	$menu_id = -1;

	while ($o = $q->obj()) {
		if ($o->folder != $fold) {
			if ($fold != "") {
				$str .= "\t\t</ul>\n\t</li>\n";
			}
			$fold = $o->folder;
			$menu_id++;
			$str .= "\t<li class='menu'>\n\t\t<a class='menu' onclick='menu_show(\"menu_$menu_id\")'>$fold</a>\n\t\t<ul id='menu_$menu_id' class='menusub'>\n";
		}
		
		if      ($o->ptype == "External") $str .= "\t\t\t". menu_new_page($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "System")   $str .= "\t\t\t". menu_tdb_page($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Client")   $str .= "\t\t\t". menu_tdb_page($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Table" )   $str .= "\t\t\t". menu_tdb_table($o->page,$o->datalink) . "\n";
		else if ($o->ptype == "Form")     $str .= "\t\t\t". menu_tdb_form($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Report")   $str .= "\t\t\t". menu_tdb_rpt ($o->page, $o->pagefile) . "\n";
	}
	$str .= "\t\t</ul>\n\t</li>\n";
	$str .= "<script>timeout_set(60);</script>
		<!-- Menu Déconnexion -->
		<li class='menu'>
			<a class='menu' onclick='document.getElementById(\"menusubmit\").submit()'>Déconnexion</a> 
		</li>
	</ul>";
	return $str;
}
