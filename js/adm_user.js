function adm_user_roles_toggle_selected(r) {
	if (r.classList.contains('selected')) {
		r.classList.remove('selected');
	} else {
		r.classList.add('selected');
	} 
}
function adm_user_roles_read() {
	var rows = document.getElementById('roles').tBodies[0].rows;
	var rids = [];
	for (r = 0; r < rows.length; r++) {
		if (rows[r].classList.contains('selected')) {
			rids.push(rows[r].id.substr('role'.length));
		}
	}
	return rids;
}
function adm_user_form_data() {
	var formd  = {};
	formd.user = {};
	formd.user.uid     = document.getElementById("uid"    ).value;
	formd.user.login   = document.getElementById("login"  ).value;
	formd.user.mail    = document.getElementById("mail"   ).value;
	formd.user.name    = document.getElementById("name"   ).value;
	formd.user.surname = document.getElementById("surname").value;

	if (document.getElementById("active").checked) formd.user.active = 'Y';
	else                                           formd.user.active = 'N';

	formd.user.since   = document.getElementById("since"  ).value;
	formd.user.until   = document.getElementById("until"  ).value;
	formd.roles        = adm_user_roles_read();

/*
	formd.user.login   = $("#login"  ).val();
	formd.user.mail    = $("#mail"   ).val();
	formd.user.name    = $("#name"   ).val();
	formd.user.surname = $("#surname").val();
	formd.user.active  = $("#active" ).prop("checked");
	formd.user.since   = $("#since"  ).val();
	formd.user.until   = $("#until"  ).val();
	formd.roles        = adm_user_roles_read();
*/
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
console.log("data: " + JSON.stringify(dat));
	return dat;
}
function adm_user_changed(dat, rol) {
	var od = document.getElementById("o_data");
	if (od == null) {
		console.log("no original data");
		return false;
	}

	console.log("original data: " + od.value);

	if (dat.login   != od.value.login)   return true;
	if (dat.mail    != od.value.mail)    return true;
	if (dat.name    != od.value.name)    return true;
	if (dat.surname != od.value.surname) return true;
	if (dat.active  != od.value.active)  return true;
	if (dat.since   != od.value.since)   return true;
	if (dat.until   != od.value.until)   return true;

	if (JSON.stringify(rol) != JSON.stringify(od.value.roles))   return true;

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
/****************************************
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
****************************************/
function adm_can_create(dat) {
	r = null;
	var o_data = document.getElementById("o_data").value;
	if (dat.login == o_data.login || dat.login == "") { // || (r =  adm_db_check("getusrdata.php", dat.login)) != null) {
		$("#create").hide();
		if (r !== null) { 
			document.getElementById("login").classList.add("conflict");
		} else  {
			document.getElementById("login").classList.remove("conflict");
		}
		return false;	
	}
	$("#create").show();
	return true;
}
function adm_user_check() {
	var dat = adm_user_data();
	var rol = adm_user_roles_read();
	if (dat.login  == "") {
		document.getElementById("login").classList.add("required");
	} else {
		document.getElementById("login").classList.remove("required");
	}
	adm_can_update(dat, rol);
	adm_can_create(dat, rol);

	/*
	if (rol.indexOf("" + r_adm) > -1) 
		$("#slav").hide();
	else
		$("#slav").show();
	*/
}
