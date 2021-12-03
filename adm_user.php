<?php
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");
require_once("lib/args.php");
require_once("lib/users.php");
require_once("lib/session.php");

global $s;

#
# Start/Restore session:
if (!isset($s)) $s = new session();

#
# Check 
$s->check();
if (!$s->has_role("admin")) exit;

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
	r_adm = $r_adm;
	function roles_toggle_selected(r) {
		if (r.classList.contains('selected')) {
			r.classList.remove('selected');
		} else {
			r.classList.add('selected');
		} 
	}
	function roles_read() {
		var rids = [];
		if (document.getElementById('apis') == null) return rids;
		var rows = document.getElementById('roles').tBodies[0].rows;
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

function apis_show($uid) {
	$out = "";
	$uapi = [];
	if ($uid != "") {
		$q = new query("select api_id from tech.user_api where user_id = '$uid'");
		while ($o = $q->obj()) {
			array_push($uapi, $o->api_id);
		}
	}
	$q = new query("select id, name from tech.api");
	$out .= "<table id='apis' class='subform' width='100%' onclick='adm_user_check()'>\n";
	$r_adm = -1;
	while ($r = $q->obj()) {
		$c = "";
		if (in_array($r->id, $uapi)) {
			$c = "class='selected'";
		}
		$out .=	"<tr id='api$r->id' onclick='apis_toggle_selected(this)' $c><td onmouseover='this.classList.add(\"tdover\")' onmouseout='this.classList.remove(\"tdover\")'>$r->name</td></tr>\n";
		if ($r->name == 'admin') $r_adm = $r->id;
		else $r_adm = 0;
	}
	$out .= "</table>\n";
	$out .= "
<script>
	r_adm = $r_adm;
	function apis_toggle_selected(r) {
		if (r.classList.contains('selected')) {
			r.classList.remove('selected');
		} else {
			r.classList.add('selected');
		} 
	}
	function apis_read() {
		var api_ids = [];
		if (document.getElementById('apis') == null || document.getElementById('apis').tBodies.length == 0) return api_ids;
		var rows = document.getElementById('apis').tBodies[0].rows;
		for (r = 0; r < rows.length; r++) {
			if (rows[r].classList.contains('selected')) {
				api_ids.push(rows[r].id.substr('api'.length));
			}
		}
		return api_ids;
	}
	
	var o_apis = apis_read();
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
		function adm_user_data() {
			uid     = $("#uid").val();
			login   = $("#login").val();
			mail    = $("#mail").val();
			name    = $("#name").val();
			surname = $("#surname").val();

			if (document.getElementById("active").checked) 
				active = 'Y';
			else 
				active = 'N';

			since   = $("#since").val();
			until   = $("#until").val();
			dat = {'uid': uid, 'login': login, 'mail': mail, 'name': name, 'surname': surname, 'active': active, 'since': since, 'until': until};
			return dat;
		}
		function adm_user_changed(dat, rol,api) {
			if (dat.login   != o_data.login)   return true;
			if (dat.mail    != o_data.mail)    return true;
			if (dat.name    != o_data.name)    return true;
			if (dat.surname != o_data.surname) return true;
			if (dat.active  != o_data.active)  return true;
			if (dat.since   != o_data.since)   return true;
			if (dat.until   != o_data.until)   return true;

			if (JSON.stringify(rol) != JSON.stringify(o_roles))   return true;
                        if (JSON.stringify(api) != JSON.stringify(o_apis))   return true;
	
			return false;
		}
		function adm_can_update(dat, rol,api) {
			if (dat.uid   ==  '' || dat.login ==  '' || !adm_user_changed(dat, rol,api)) {
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
					$("#login").addClass("conflict");
				} else  {
					$("#login").removeClass("conflict");
				}
				
				return false;	
			}
			$("#create").show();
			return true;
		}
		function adm_user_check() {
			var dat = adm_user_data();
			var rol = roles_read();
			var api = apis_read();
			if (dat.login  == "") {
				$("#login").addClass("required");
			} else {
				$("#login").removeClass("required");
			}
			adm_can_update(dat, rol,api);
			adm_can_create(dat, rol,api);

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
        print("<tr><th>API  </th><td><div class='scrollable'>" . apis_show($uid)   . "</div></td></tr>\n");
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
	?>
		<script> 
		function adm_user_form_data() {
			var formd  = {};
			formd.user = {};
			formd.user.uid     = $("#uid"    ).val();
			formd.user.login   = $("#login"  ).val();
			formd.user.mail    = $("#mail"   ).val();
			formd.user.name    = $("#name"   ).val();
			formd.user.surname = $("#surname").val();
			formd.user.active  = $("#active" ).prop('checked');
			formd.user.since   = $("#since"  ).val();
			formd.user.until   = $("#until"  ).val();
			formd.roles        = roles_read();
			formd.apis         = apis_read();
			return formd;
		}
		function adm_user_cancel() {
			$("#data_area").load('adm_user.php', null);
		}
		function adm_user_open(id) {
			$("#data_area").load('adm_user.php', {'uid': id });
		}
		function adm_user_new() {
			$("#data_area").load('adm_user.php', {'newuser': true });
		}
		function adm_user_create() {
			var data = adm_user_form_data();
			$("#data_area").load('adm_user.php', {'act': 'create', 'data': data});
		}
		function adm_user_update() {
			var data = adm_user_form_data();
			$("#data_area").load('adm_user.php', {'act': 'update', 'data': data});
		}
		</script>
	<?php
	$q = new query("select * from tech.user");
	print ("<table class='form'>\n");
	$hdr = "<tr><th>#</th><th>Login</th><th>Email</th><th>Actif</th><th>Depuis</th><th>Jusqu'à</th></tr>\n";
	print("$hdr\n");

	if ($q->nrows() == 0) {
		print("<tr><td class='hdr' colspan='".(count($fields) + 1)."'>Pas de données</td></tr>\n");
	} else {
		$i = 1;
		while ($o = $q->obj()) {
			if ($i % 2) $odd = "class='odd'";
			else        $odd = "";
			print("<tr $odd onmouseover='this.normalClassName=this.className;this.className=\"over\"' onmouseout='this.className=this.normalClassName;' onclick='adm_user_open(\"$o->id\")'>");
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
	print("<tr><td colspan='6'><input type='button' value='Créer' onclick='adm_user_new();'/></td><tr>\n");
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
		if ($usr->active) $active = 'Y';
		else              $active = 'N';
		if ($usr->since != '') 
			$since = date_human_to_db($usr->since);
		else 
			$since = '';
		if ($usr->until != '') 
			$until = date_human_to_db($usr->until);
		else 
			$until = "";

                
		$rol = $d->roles;
		if (property_exists($d, "apis")) 
			$api = $d->apis;
		else 
			$api = (object)[];

		if ($act == "create") {
			if (($nid = adm_user_new_id()) === false) {
				dbg_err("cannot find a new id");
			} else {
				if ($until == "") $until = "null";
				else $until = "'$until'";
				$sql = "insert into tech.user values ('$nid', '$usr->login', '', '$usr->name', '$usr->surname', '$usr->mail', '$active', '$since', $until)";
				new query($sql);

				foreach ($rol as $r)  new query("insert into tech.user_role values ($nid, '$r')");
				foreach ($api as $a)  new query("insert into tech.user_api  values ($nid, '$a')");
				$auth = new auth_local();
				$auth->update($usr->login, 'ChangeMe');
				#print("<pre>sql = $sql\n</pre>\n");
			}
		} else if ($act == "update") {
			$sql = "update tech.user set login = '$usr->login', mail = '$usr->mail', name = '$usr->name', surname = '$usr->surname', mail = '$usr->mail', active = '$active', since = '$since', until = '$until' where id = '$usr->uid'";
			new query($sql);

			new query("delete from tech.user_role where user_id = $usr->uid");
			foreach ($rol as $r)  new query("insert into tech.user_role values ($usr->uid, $r)");
                        
                        new query("delete from tech.user_api where user_id = $usr->uid");
			foreach ($api as $a) new query("insert into tech.user_api values ($usr->uid, $a)");
			#print("<pre>sql = $sql\n</pre>\n");
		} 
	}
	adm_user_list();
}
print("</div>\n");
?>
