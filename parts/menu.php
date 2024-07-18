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


	$menu_cur = $menu_entry_cur = $menu_data_cur = null;
	foreach (["menu_cur", "entry_cur", "data_cur" ] as $v) {
		${$v} = null;
		if ($_session_->has($v)) {
			${$v} = $_session_->getvar($v);	
		}
	}
	if (is_string($menu_cur) && is_string($entry_cur) && is_string($data_cur)) {
		$initf = "menu_restore('$menu_cur', '$entry_cur', '$data_cur');";
	} else
		$initf = "";

	$rstr = "";

	foreach ($roles as $r) {
		if ($rstr != "") {
			$rstr .= ", ";
		}
		$rstr .= "'$r'";
	}

	if ($rstr == "") $rstr = "'user'";

	function menu_table($label, $dlink) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" target=\"_blank\" onclick=\"menu_table(this, '$l', '$dlink');\">$label</a></li>";
	}
	function menu_external($label, $url) {
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" target=\"_blank\" href='$url'>$label</a></li>";
	}
	function menu_page($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" onclick=\"menu_page(this, '$url', '$label');\">$label</a></li>";
	}
	function menu_view($label, $entity) {
		return "\t\t\t<li class='menuentry'><a id='me_$label' class='menuentry' onclick='menu_view(this, \"$label\", \"$entity\");'>$label</a></li>\n";
	}
	function menu_hdr($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" onclick=\"menu_hdr(this, '$url', '$l');\">$label</a></li>";
	}
	function menu_rpt($label, $rptname) {
		$rn = menu_esc($rptname);
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" onclick=\"menu_rpt(this, '$rn');\">$label</a></li>";
	}
	function menu_form($label, $url) {
		$l = menu_esc($label);
		return "<li class=\"menuentry\"><a id='me_$label' class=\"menuentry\" onclick=\"menu_form(this, '$url', '$l');\">$label</a></li>";
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
		
		if      ($o->ptype == "External") $str .= "\t\t\t". menu_external($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "System")   $str .= "\t\t\t". menu_page($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Client")   $str .= "\t\t\t". menu_page($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Table" )   $str .= "\t\t\t". menu_table($o->page,$o->datalink) . "\n";
		else if ($o->ptype == "Form")     $str .= "\t\t\t". menu_form($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "Report")   $str .= "\t\t\t". menu_rpt ($o->page, $o->pagefile) . "\n";
		else if ($o->ptype == "View")     $str .= "\t\t\t". menu_view($o->page, $o->datalink) . "\n";
	}
	$str .= "\t\t</ul>\n\t</li>\n";
	$str .= "<script>timeout_set(60);$initf</script>
		<!-- Menu Déconnexion -->
		<li class='menu'>
			<a class='menu' onclick='document.getElementById(\"menusubmit\").submit()'>Déconnexion</a> 
		</li>
	</ul>";
	return $str;
}
