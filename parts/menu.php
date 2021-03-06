<?php
require_once("lib/dbg_tools.php");
require_once("lib/session.php");
require_once("lib/query.php");

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

	function menu_new_table($label, $dlink) {
		return "\t\t\t<li class='menuentry'><a class='menuentry' target='_blank' onclick='tdb_table(\"$label\", \"$dlink\");menu_onclick(this);'>$label</a></li>\n";
	}
	function menu_new_page($label, $url) {
		return "\t\t\t<li class='menuentry'><a class='menuentry' target='_blank' href='$url'>$label</a></li>\n";
	}
	function menu_tdb_page($label, $url) {
		return "\t\t\t<li class='menuentry'><a class='menuentry' onclick='tdb_page(\"$url\", \"$label\");menu_onclick(this);'>$label</a></li>\n";
	}
	function menu_tdb_hdr($label, $url) {
		return "\t\t\t<li class='menuentry'><a class='menuentry' onclick='tdb_hdr(\"$url\",  \"$label\");menu_onclick(this);'>$label</a></li>\n";
	}
	function menu_tdb_form($label, $url) {
		return "\t\t\t<li class='menuentry'><a class='menuentry' onclick='tdb_form(\"$url\",  \"$label\");menu_onclick(this);'>$label</a></li>\n";
	}
	$str = '<form id="menusubmit"  method="post" action="">
		<input type="hidden" name="page" value="logout"/>
		</form>
		<ul id="menuul" class="menu">';

	$q = new query("select f.name as folder ,p.name as page ,p.ptype ,p.datalink ,p.pagefile ,fp.perm_name as perm from tech.folder f ,tech.folder_page pf ,tech.folder_perm fp ,tech.page p ,tech.role r where f.id = pf.folder_id and p.id = pf.page_id and fp.folder_id = f.id and r.id = fp.role_id and r.name in ('admin', 'user', 'local') order by f.id, pf.page_order");

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

		if 	    ($o->ptype    == "Table")    $str .= menu_new_table($o->page, $o->datalink); 
		else if ($o->ptype    == "System")   $str .= menu_tdb_page($o->page,  $o->pagefile); 
		//if      ($o->launcher == "new page") $str .= menu_new_page($o->label, $o->script);
		//else if ($o->launcher == "tdb_page") $str .= menu_tdb_page($o->label, $o->script);
		//else if ($o->launcher == "tdb_hdr" ) $str .= menu_tdb_hdr ($o->label, $o->script);
		//else if ($o->launcher == "tdb_form") $str .= menu_tdb_form($o->label, $o->script);
	}
	$str .= "\t\t</ul>\n\t</li>\n";
	$str .= "<script>timeout_set(10);</script>
		<script>
		
		</script>
		<!-- Menu D??connexion -->
		<li class='menu'>
			<a class='menu' onclick='document.getElementById(\"menusubmit\").submit()'>D??connexion</a> 
		</li>
	</ul>";
	return $str;
}
