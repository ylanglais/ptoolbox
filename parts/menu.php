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

	$q = new query("select c.category as category, p.name as name, p.label as label, p.script as script, p.launcher as launcher from tech.page p, tech.page_category c where p.category  = c.category and id in (select page_id from tech.permission where role in ($rstr)) order by c.num, p.id ");

	$cat = "";

	$menu_id = -1;

	while ($o = $q->obj()) {
		if ($o->category != $cat) {
			if ($cat != "") {
				$str .= "\t\t</ul>\n\t</li>\n";
			}
			$cat = $o->category;
			$menu_id++;
			$str .= "\t<li class='menu'>\n\t\t<a class='menu' onclick='menu_show(\"menu_$menu_id\")'>$cat</a>\n\t\t<ul id='menu_$menu_id' class='menusub'>\n";
		}

		if      ($o->launcher == "new page") $str .= menu_new_page($o->label, $o->script);
		else if ($o->launcher == "tdb_page") $str .= menu_tdb_page($o->label, $o->script);
		else if ($o->launcher == "tdb_hdr" ) $str .= menu_tdb_hdr ($o->label, $o->script);
		else if ($o->launcher == "tdb_form") $str .= menu_tdb_form($o->label, $o->script);
	}
	$str .= "\t\t</ul>\n\t</li>\n";
	$str .= "<script>timeout_set(10);</script>
		<script>
		
		</script>
		<!-- Menu Déconnexion -->
		<li class='menu'>
			<a class='menu' onclick='document.getElementById(\"menusubmit\").submit()'>Déconnexion</a> 
		</li>
	</ul>";
	return $str;
}
