function adm_user_roles_toggle_selected(r) {
	if (r.classList.contains('selected')) {
		r.classList.remove('selected');
	} else {
		r.classList.add('selected');
	} 
}
function adm_user_roles_read() {
	var rows = el('roles').tBodies[0].rows;
	var rids = [];
	for (r = 0; r < rows.length; r++) {
		if (rows[r].classList.contains('selected')) {
			rids.push(parseInt(rows[r].id.substr('role'.length)));
		}
	}
	return rids.sort(function(a, b) {return a - b;});
}
function adm_user_form_data() {
	var formd  = {};
	formd.user = {};
	formd.user.uid     = el("uid"    ).value;
	formd.user.login   = el("login"  ).value;
	formd.user.mail    = el("mail"   ).value;
	formd.user.name    = el("name"   ).value;
	formd.user.surname = el("surname").value;

	if (el("active").checked) formd.user.active = true;
	else                                           formd.user.active = false;

	formd.user.since   = el("since"  ).value;
	formd.user.until   = el("until"  ).value;
	formd.roles        = adm_user_roles_read();

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
	uid     = el("uid").value;
	login   = el("login").value;
	mail    = el("mail").value;
	name    = el("name").value;
	surname = el("surname").value;

	if (el("active").checked) 
		active = true;
	else 
		active = false;

	since   = el("since").value;
	until   = el("until").value;
	dat = {'uid': uid, 'login': login, 'mail': mail, 'name': name, 'surname': surname, 'active': active, 'since': since, 'until': until};
	return dat;
}
function adm_user_changed(dat, rol) {
	var od = JSON.parse(el("o_data").value);
	if (od == null) {
		dbg("no original data");
		return false;
	}

	if (dat.login   != od.login)   return true;
	if (dat.mail    != od.mail)    return true;
	if (dat.name    != od.name)    return true;
	if (dat.surname != od.surname) return true;
	if (dat.active  != od.active)  return true;
	if (dat.since   != od.since)   return true;
	if (dat.until   != od.until)   return true;
	if (JSON.stringify(rol) != JSON.stringify(od.roles))  return true;

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
	var o_data = el("o_data").value;
	if (dat.login == o_data.login || dat.login == "") { // || (r =  adm_db_check("getusrdata.php", dat.login)) != null) {
		$("#create").hide();
		if (r !== null) { 
			el("login").classList.add("conflict");
		} else  {
			el("login").classList.remove("conflict");
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
		el("login").classList.add("required");
	} else {
		el("login").classList.remove("required");
	}
	adm_can_update(dat, rol);
	adm_can_create(dat, rol);
}
