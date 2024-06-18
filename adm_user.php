<?php
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");

global $_session_;

#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();

#
# Check 
$_session_->check();
if (!$_session_->has_role("admin")) exit;

function adm_user_new_id() {
	$q = new query("select max(id)+1 as newid from tech.user");
	if ($q->nrows() < 1) {
		return false;
	}
	return $q->obj()->newid;
}

function roles_show($uid, $ur) {
	$out = "";
	$q = new query("select id, name from tech.role");
	$roles = [];
	$out .= "<table id='roles' class='subform' width='100%' onclick='adm_user_check()'>\n";
	while ($r = $q->obj()) {
		$c = "";
		if (in_array($r->id, $ur)) {
			$c = "class='selected'";
		}
		$out .=	"<tr id='role$r->id' onclick='adm_user_roles_toggle_selected(this)' $c><td onmouseover='this.classList.add(\"tdover\")' onmouseout='this.classList.remove(\"tdover\")'>$r->name</td></tr>\n";
	}
	$out .= "</table>\n";
	return $out;
}

#
#
function adm_user_ui($a, $uid) {
	$roles  = [];
	if ($uid === false) {
		$uid = "";
		if (($login   = $a->post("login"))   === false) $login   = "";
		if (($mail    = $a->post("mail"))    === false) $mail    = ""; 
		if (($name    = $a->post("name"))    === false) $name    = ""; 
		if (($surname = $a->post("surname")) === false) $surname = ""; 
		if (($active  = $a->post("active"))  === false) $active  = ""; 
		if (($since   = $a->post("since"))   === false) $since   = date_db_to_human(today());
		if (($until   = $a->post("until"))   === false) $until   = ""; 
		$roles = [];
	} else {
		$q       = new query("select * from tech.user where id = '$uid'");
		$o       = $q->obj();
		$login   = $o->login;
		$mail    = $o->mail;
		$name    = $o->name;
		$surname = $o->surname;
		$active  = $o->active;
		if ($o->since != "") $since = date_db_to_human($o->since);
		else                 $since = "";
		if ($o->until != "") $until = date_db_to_human($o->until);
		else                 $until = "";

		$q = new query("select role_id from tech.user_role where user_id = '$uid' order by 1");
		while ($o = $q->obj()) {
			array_push($roles, $o->role_id);
		}
	};

	if ($active === true) $act = "true";
	else                  $act = "false";

	$data = "{ \"uid\": \"$uid\", \"login\": \"$login\", \"mail\": \"$mail\", \"name\": \"$name\", \"surname\": \"$surname\", \"active\": $act, \"since\": \"$since\", \"until\": \"$until\", \"roles\": [".implode(',', $roles)."]  }";
	print("<div style='height: 100%'>");
	print("<input type='hidden' id='o_data' value='". $data ."'/>");
	print("<input type='hidden' id='uid' value='$uid'/>");
	print("<table class='form' id='mastr'>\n");
	if ($login == "") 
		$req="class='required'";
	else 
		$req = "";

	print("<tr><th><label for='login'>Login</label></th><td><input id='login'   type='text' value='$login'   $req onchange='adm_user_check();'/></td></tr>");
	print("<tr><th><label for='mail' >Email</label></th><td><input id='mail'    type='text' value='$mail'         onchange='adm_user_check();'/></td></tr>");
	print("<tr><th><label for='name'>Prénom</label></th><td><input id='name'    type='text' value='$name'    $req onchange='adm_user_check();'/></td></tr>");
	print("<tr><th><label for='surname'>Nom</label></th><td><input id='surname' type='text' value='$surname' $req onchange='adm_user_check();'/></td></tr>");
	$chk=""; if ($active === true) $chk="checked";
	print("<tr><th><label for='active'>Actif</label></th><td><input id='active' type='checkbox' $chk onchange='adm_user_check();'/></td></tr>");

	print("<tr><th><label for='since'>Depuis </label></th><td><input id='since' type='text' name='since' size='16' pattern='[0-3][0-9]/[0-1][0-9]/[12][0-9][0-9][0-9]' placeholder='jj/mm/aaaa' value='$since' onchange='adm_user_check();'/></td><tr>\n");
	print("<tr><th><label for='until'>Jusqu'à</label></th><td><input id='until' type='text' name='until' size='16' pattern='[0-3][0-9]/[0-1][0-9]/[12][0-9][0-9][0-9]' placeholder='jj/mm/aaaa' value='$until' onchange='adm_user_check();'/></td><tr>\n");

	print("<tr><th>Roles  </th><td><div class='scrollable'>" . roles_show($uid, $roles)   . "</div></td></tr>\n");
	print("<tr><td colspan='2'>\n");
	print("<input id='create' type='button' value='Créer' onclick='adm_user_create();'/>");
	if ($uid != "") { 
		print("<input id='update' type='button' value='Modifier' onclick='adm_user_update();' style='display: none'/>");
	} 
	print("<input type='button' value='Annuler' onclick='adm_user_cancel();'/>");
	print("</table>\n");
	print("</div>\n");
	print("</div>\n");
}

function adm_user_list() {
	$q = new query("select * from tech.user where active = true order by id");
	print ("<table class='glist'>\n");
	$hdr = "<tr><th>#</th><th>Login</th><th>Email</th><th>Actif</th><th>Depuis</th><th>Jusqu'à</th></tr>\n";
	print("$hdr\n");

	if ($q->nrows() == 0) {
		print("<tr><td class='hdr' colspan='".(count($fields) + 1)."'>Pas de données</td></tr>\n");
	} else {
		$i = 1;
		while ($o = $q->obj()) {
			print("<tr onmouseover='this.normalClassName=this.className;this.className=\"over\"' onmouseout='this.className=this.normalClassName;' onclick='adm_user_open(\"$o->id\")'>");
			if ($o->since != "") $since = date_db_to_human($o->since);
			else                 $since = '';
			if ($o->until != "") $until = date_db_to_human($o->until);
			else                 $until = '';
			if ($o->active === true) $active = "&#9745;"; 
			else                     $active = "&#9744;";

			print("<td class='num'>$i</td><td>$o->login</td><td>$o->mail</td><td align='center'>$active</td><td align='center'>$since</td><td align='center'>$until</td></tr>\n");
			print("</tr>\n");
			$i++;
		}
	}
	print("$hdr\n");
	print("<tr class='but'><td colspan='6'><input type='button' value='Créer' onclick='adm_user_new();'/></td><tr>\n");
	print("</table>\n");
}


print("<div id='user'>\n"); 
print("<h1>Gestion des utilisateurs</h1><br/>\n");
$a = new args(); 
if (($uid = $a->post("uid")) !== false || $a->post("newuser") == true) {
	adm_user_ui($a, $uid);
} else {
	if (($act = $a->post("act")) !== false) {
		$d   = (object) $a->post("data");	
		$usr = (object) $d->user;
		if ($usr->active === true) $active = 'true';
		else  						$active = 'false';
		if ($usr->since != '') 
			$since = "'". date_human_to_db($usr->since) . "'";
		else 
			$since = "null";
		if ($usr->until != '') 
			$until = "'". date_human_to_db($usr->until) . "'";
		else 
			$until = "null";

		$rol = $d->roles;

		if ($act == "create") {
			if (($nid = adm_user_new_id()) === false) {
				dbg_err("cannot find a new id");
			} else {
				$sql = "insert into tech.user values ('$nid', '$usr->login', '', '$usr->name', '$usr->surname', '$usr->mail', '$active', $since, $until)";
				new query($sql);

				foreach ($rol as $r)  new query("insert into tech.user_role values ($nid, '$r')");
				$auth = new auth_local();
				$auth->update($usr->login, 'ChangeMe');
				#audit_action("tech.user", "
			}
		} else if ($act == "update") {
			$sql = "update tech.user set login = '$usr->login', mail = '$usr->mail', name = '$usr->name', surname = '$usr->surname', active = $active, since = $since, until = $until where id = '$usr->uid'";
			new query($sql);

			new query("delete from tech.user_role where user_id = $usr->uid");
			foreach ($rol as $r)  new query("insert into tech.user_role values ($usr->uid, $r)");
		} 
	}
	adm_user_list();
}
print("</div>\n");
?>
