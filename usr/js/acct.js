function acct_part_reload() {
	dbgp();
}
function acct_particulier_onbeforeunload() {
/*
	dbg("in acct_particulier_onbeforeunload");
	dat= acct_read_par_data();
	data.action = "acct_par_create";
	data.data   = dat;
	menu_user_data_set(data);
*/
}
function acct_login() {
	if ((e = el("email")) === null) return; 
	if (e.value == "") {
		el("message").innerHTML = "<p><b>le mail doit etre saisi</b></p>";
		return;
	}
	login = e.value;
	if ((e = el("passwd")) === null) return; 
	if (e.value == "") {
		el("message").innerHTML = "<p><b>le mot de passe doit etre saisi</b></p>";
		return;
	}
	passwd = e.value;
	ctrl('acct', {'login': login, 'passwd': passwd}, 'data_area');
	dbg("reload menu");
	ctrl('menu', {'reload': true}, 'menu');
	dbg("menu reloaded");
}
function acct_siren_data() {
	data = {'action': 'acct_siren_data', 'siren': el('siren').value};
	ctrl('acct', data, 'siren_data'); 
}
function acct_pro_contact(data) {
	data = {'action': 'acct_pro_contact', 'data': data}, 
	ctrl('acct', data, 'contactdata');
}
function acct_pro_new() {
	data = {'action': 'acct_pro_new'}
	ctrl('acct', data, 'data_area');
}
function acct_pro_post() {
}
function acct_par_new() {
	data = {'action': 'acct_par_new'}
	ctrl('acct', data, 'data_area');
}
function acct_label_from_input(inp) {
	lbls = els_by_tag('label');
	for (l of lbls) {
		if (l.htmlFor == inp.id) return l.innerHTML;
	}	
	return inp.id;
}
function acct_read_par_data() {
	let flds = [ "title", "firstname", "lastname", "phone", "email", 
	"line1", "line2", "line3", "line4",
	"zipcode", "city", "country", "passwd1", "passwd2" ];

	let ok = true;

	data        = {};
	data.errors = [];
	data.state  = "ok";

	for (f of flds) {
		dbg("field: " +f);
		if ((e = el(f)) !== null) {
			dbg("e.id: " + e.id + ", e.value: " + e.value);
			if (e.classList.contains("required") && e.value == "") {
				data.state = "ko";
				data.errors.push("Le champ \"" + acct_label_from_input(e) + "\" doit etre rempli");
			}
			data[f] = e.value;
		} 
	}
	dbg(data);
	if (data.passwd1 != data.passwd2) {
		data.state = "ko";
		data.errors.push("Le champ \"" + acct_label_from_input(e) + "\" doit etre rempli");
	}
	return data;
}
function acct_par_create() {
	data = acct_read_par_data();
	dbg(data);
	if (data.state != "ok") {
		alert("Ce formulaire contient les erreurs suivantes: \n\t- " + data.errors.join("\n\t- "));
		return;
	}
	ctrl("acct", {"action": "acct_par_create", "data": data}, 'data_area');
}
