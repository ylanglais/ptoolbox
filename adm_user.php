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

function roles_show($uid) {
	$out = "";
	$ur = [];
	if ($uid != "") {
		$q = new query("select role_id from tech.user_role where user_id = '$uid'");
		while ($o = $q->obj()) {
			array_push($ur, $o->role_id);
		}
	}
	$q = new query("select id, name from tech.role");
	$roles = [];
	$out .= "<table id='roles' class='subform' width='100%' onclick='adm_user_check()'>\n";
	$r_adm = -1;
	while ($r = $q->obj()) {
		$c = "";
		if (in_array($r->id, $ur)) {
			$c = "class='selected'";
		}
		$out .=	"<tr id='role$r->id' onclick='roles_toggle_selected(this)' $c><td onmouseover='this.classList.add(\"tdover\")' onmouseout='this.classList.remove(\"tdover\")'>$r->name</td></tr>\n";
		if ($r->name == 'admin') $r_adm = $r->id;
		else $r_adm = 0;
	}
	$out .= "</table>\n";
	$out .= "
<script>
console.log('in 1');
	r_adm = $r_adm;
	function roles_toggle_selected(r) {
		if (r.classList.contains('selected')) {
			r.classList.remove('selected');
		} else {
			r.classList.add('selected');
		} 
	}
	function roles_read() {
		var rows = document.getElementById('roles').tBodies[0].rows;
		var rids = [];
		for (r = 0; r < rows.length; r++) {
			if (rows[r].classList.contains('selected')) {
				rids.push(rows[r].id.substr('role'.length));
			}
		}
		return rids;
	}	
	var o_roles = roles_read();
</script>
";
	return $out;
}

#
#
function adm_user_ui($a, $uid) {
	if ($uid === false) {
		$uid = "";
		if (($login   = $a->post("login"))   === false) $login   = "";
		if (($mail    = $a->post("mail"))    === false) $mail    = ""; 
		if (($name    = $a->post("name"))    === false) $name    = ""; 
		if (($surname = $a->post("surname")) === false) $surname = ""; 
		if (($active  = $a->post("active"))  === false) $active  = ""; 
		if (($since   = $a->post("since"))   === false) $since   = date_db_to_human(today());
		if (($until   = $a->post("until"))   === false) $until   = ""; 
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
	};

	$data = "{ 'uid': '$uid', 'login': '$login', 'mail': '$mail', 'name': '$name', 'surname': '$surname', 'active': '$active', 'since': '$since', 'until': '$until' }";
	print("<div style='height: 100%'>");
	print("<script>var o_data = $data;\n</script>\n");	
	?>
		<script>
		console.log("in 2");
		function adm_user_data() {
			uid     = document.getElementById("uid").value;
			login   = document.getElementById("login").value;
			mail    = document.getElementById("mail").value;
			name    = document.getElementById("name").value;
			surname = document.getElementById("surname").value;

			if (document.getElementById("active").checked) 
				active = 'Y';
			else 
				active = 'N';

			since   = document.getElementById("since").value;
			until   = document.getElementById("until").value;
			dat = {'uid': uid, 'login': login, 'mail': mail, 'name': name, 'surname': surname, 'active': active, 'since': since, 'until': until};
			return dat;
		}
		function adm_user_changed(dat, rol) {
			if (dat.login   != o_data.login)   return true;
			if (dat.mail    != o_data.mail)    return true;
			if (dat.name    != o_data.name)    return true;
			if (dat.surname != o_data.surname) return true;
			if (dat.active  != o_data.active)  return true;
			if (dat.since   != o_data.since)   return true;
			if (dat.until   != o_data.until)   return true;

			if (JSON.stringify(rol) != JSON.stringify(o_roles))   return true;
	
			return false;
		}
		function adm_can_update(dat, rol) {
			if (dat.uid   ==  '' || dat.login ==  '' || !adm_user_changed(dat, rol)) {
				$("#update").hide();
				return false;
			}
			$("#update").show();
			return true;
		}
		function adm_db_check(url, login) {
			var r = $.ajax({ 
				type: "POST",
				url: url, 
				dataType: 'json', 
				data: {'login': login}, 
				async: false, 
			}).responseJSON;
			return r;
		}
		function adm_can_create(dat) {
			r = null;
			if (dat.login == o_data.login || dat.login == "" || (r =  adm_db_check("getusrdata.php", dat.login)) != null) {
				$("#create").hide();
				if (r !== null) { 
					document.getElementById("login").classList.add("conflict");
				} else  {
					document.getElementById("login")classList..remove("conflict");
				}
				
				return false;	
			}
			$("#create").show();
			return true;
		}
		function adm_user_check() {
			var dat = adm_user_data();
			var rol = roles_read();
			if (dat.login  == "") {
				document.getElementById("login").classList.add("required");
			} else {
				document.getElementById("login").classList.remove("required");
			}
			adm_can_update(dat, rol);
			adm_can_create(dat, rol);

			if (rol.indexOf("" + r_adm) > -1) 
				$("#slav").hide();
			else
				$("#slav").show();
		}
		</script>
	<?php
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
	if ($active = "" || $active = true) 
		$chk="checked";
	else  
		$chk="";
	print("<tr><th><label for='active'>Actif</label></th><td><input id='active' type='checkbox' $chk onchange='adm_user_check();'/></td></tr>");

	print("<tr><th><label for='since'>Depuis</label></th><td><input id='since' type='text' name='since'size='16' pattern='[0-3][0-9]/[0-1][0-9]/[12][0-9][0-9][0-9]' placeholder='jj/mm/aaaa' value='$since' onchange='adm_user_check();'/></td><tr>\n");
	print("<tr><th><label for='until'>Jusqu'à</label></th><td><input id='until' type='text' name='until'  size='16' pattern='[0-3][0-9]/[0-1][0-9]/[12][0-9][0-9][0-9]' placeholder='jj/mm/aaaa' value='$until' onchange='adm_user_check();'/></td><tr>\n");

	print("<tr><th>Roles  </th><td><div class='scrollable'>" . roles_show($uid)   . "</div></td></tr>\n");
	print("<tr><td colspan='2'>\n");
	print("<input id='create' type='button' value='Créer' onclick='adm_user_create();'/>");
	if ($uid != "") { 
		print("<input id='update' type='button' value='Modifier' onclick='adm_user_update();'/>");
	} 
	print("<input type='button' value='Annuler' onclick='adm_user_cancel();'/>");
	print("</table>\n");
?>
<script>
adm_user_check();
$("#slavdiv").height("" + (parseInt($("#mastr").height()) - 10) + "px");
$("#slav").height($("#mastr").height());
</script>
<?php
	print("</div>\n");
	print("</div>\n");
}

function adm_user_list() {
	$q = new query("select * from tech.user");
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
			print("<td class='num'>$i</td><td>$o->login</td><td>$o->mail</td><td align='center'>$o->active</td><td align='center'>$since</td><td align='center'>$until</td></tr>\n");

			print("</tr>\n");
			$i++;
		}
	}
	print("$hdr\n");
	print("<tr class='but'><td colspan='6'><input type='button' value='Créer' onclick='adm_user_new();'/></td><tr>\n");
	print("</table>\n");
}

?>
<div id='user'>
<script>
console.log("in 3");
function adm_user_form_data() {
	var formd  = {};
	formd.user = {};
	formd.user.uid     = document.getElementById("uid").value;
	formd.user.login   = document.getElementById("login").value;
	formd.user.mail    = document.getEmementById("mail").value;
	formd.user.name    = document.getElementById("name" ).value;
	formd.user.surname = document.getElementById("surname" ).value;
	formd.user.active  = document.getElementById("#active" ).checked
	formd.user.since   = document.getElemen("since"  ).value;
	formd.user.until   = document.getElemen("until"  ).value;
	formd.roles        = roles_read();
	return formd;
}
function adm_user_cancel() {
	load("data_area", "adm_user.php", null);
}
function adm_user_open(id) {
	load("data_area", "adm_user.php", {"uid": id });
}
function adm_user_new() {
	load("data_area", "adm_user.php", {"newuser": true });
}
function adm_user_create() {
	var data = adm_user_form_data();
	load("data_area", "adm_user.php", {"act": "create", "data": data});
}
function adm_user_update() {
	var data = adm_user_form_data();
	load("data_area", "adm_user.php", {"act": "update", "data": data});
}
</script>
<h1>Gestion des utilisateurs</h1><br/>

<?php
$a = new args(); 
if (($uid = $a->post("uid")) !== false || $a->post("newuser") == true) {
	adm_user_ui($a, $uid);
} else {
	if (($act = $a->post("act")) !== false) {
		$d   = (object) $a->post("data");	
		$usr = (object) $d->user;
		if ($usr->active) $active = 'Y';
		else              $active = 'N';
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
				#print("<pre>sql = $sql\n</pre>\n");
			}
		} else if ($act == "update") {
			$sql = "update tech.user set login = '$usr->login', mail = '$usr->mail', name = '$usr->name', surname = '$usr->surname', active = '$active', since = $since, until = $until where id = '$usr->uid'";
			new query($sql);

			new query("delete from tech.user_role where user_id = $usr->uid");
			foreach ($rol as $r)  new query("insert into tech.user_role values ($usr->uid, $r)");
			#print("<pre>sql = $sql\n</pre>\n");
		} 
	}
	adm_user_list();
}
print("</div>\n");
?>
