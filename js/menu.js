var menu_cur  = null;
var entry_cur = null;
var data_cur  = null;
var user_data = null;

var mh = 0;
var uh = 0;
var hh = 0

window.addEventListener('resize', menu_onresize, true);
window.addEventListener('load',   menu_onresize, true);

function menu_restore(cur_menu, cur_entry, cur_data, user_data) {
dbgp();
	if (cur_menu === null || cur_menu == "") return;

	if ((menu_cur  = el(cur_menu)) === null) return;

	menu_show(menu_cur.id);

	if (cur_entry != null) {
		entry_cur = el(cur_entry);
		data_cur  = json_decode(cur_data);
		user_data = json_decode(user_data);
		menu_data_reload();
	}
}
function menu_onresize() {
	mh = parseInt(el("menu").offsetHeight);
	uh = parseInt(el("menuul").offsetHeight);

	hh = mh - uh;

	if (menu_cur != null)
		menu_cur.style.maxHeight = "" + hh + "px";
}

function menu_show(id) {
	e = el(id);
	if (menu_cur != null) {
		menu_cur.classList.remove("current");
		menu_cur = null;
	}
	menu_cur = e;
	var da = el("data_area");
	if (da != null) 
		da.innerHTML = "";

	/* limit max-height: */
	e.style.maxHeight = "" + hh + "px";
	e.classList.add("current");
	menu_cur = e;
}

function menu_entry(e) {
	if (entry_cur != null) {
		entry_cur.classList.remove("current");
	}
	e.classList.add("current");
	entry_cur = e;
}
function menu_save(data) {
dbgp();
	ctrl("menu", data, null, false); 
}
function menu_user_data_get() {
	return user_data;
}
function menu_user_data_set(data) {
	user_data = data;
	if (menu_cur != null)
		menu_save({'menu_cur': menu_cur.id, 'entry_cur': entry_cur.id, 'data_cur': json_encode(data_cur), 'user_data': json_encode(user_data)})
}
function data_cur_set(data) {
	data_cur = data;
	dbg("user_data", user_data);
	if (menu_cur != null)
		menu_save({'menu_cur': menu_cur.id, 'entry_cur': entry_cur.id, 'data_cur': json_encode(data), 'user_data': json_encode(user_data)});	
}
function menu_form(e, fname, titre) {
	menu_entry(e);
	ctrl('form', {'fname': fname, 'titre': titre}, 'data_area');
	data_cur_set({"type": "form", "name": fname, "titre": titre});
}
function menu_rpt(e, rpt_name) {
	menu_entry(e);
	progress('data_area', rpt_name);
	ctrl('rpt', {'rpt_name': rpt_name}, 'data_area');
	data_cur_set({"type": "rpt", "rpt_name": rpt_name});
}
function menu_page(e, page) {
	menu_entry(e);
dbg(user_data);
	load("data_area", page, user_data); 
	data_cur_set({"type": "page", "page": page});
}
function menu_table(e, page, datalink) {
	menu_entry(e);
	ctrl("gui", {"page": page, "datalink": datalink}, "data_area"); 
	data_cur_set({"type": "table", "page": page, "datalink": datalink});
}
function menu_view(e, page, datalink) {
	menu_entry(e);
	ctrl("gui", {"page": page, "type": "view", "datalink": datalink}, "data_area"); 
	data_cur_set({"type": "view", "page": page, "datalink": datalink});
}
function menu_data_reload() {
	switch (data_cur.type) {
	case 'form':
		menu_form(entry_cur, data_cur.name, data_cur.titre);
		break;
	case 'rpt':
		menu_rpt(entry_cur, data_cur.rpt_name);
		break;
	case 'page':
		menu_page(entry_cur, data_cur.page)
		break;
	case 'table':
		menu_table(entry_cur, data_cur.page, data_cur.datalink)
		break;
	case 'view':
		menu_view(entry_cur, data_cur.page, data_cur.datalink)
		break;
	}
}
