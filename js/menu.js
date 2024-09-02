var menu_cur  = null;
var entry_cur = null;
var data_cur  = null;

var mh = 0;
var uh = 0;
var hh = 0

window.addEventListener('resize', menu_onresize, true);
window.addEventListener('load',   menu_onresize, true);


function menu_restore(cur_menu, cur_entry, cur_data) {
	if (cur_menu == null || cur_menu == "") return;

	menu_cur  = document.getElementById(cur_menu);
	menu_show(menu_cur.id);

	if (cur_entry != null) {
		entry_cur = document.getElementById(cur_entry);
		data_cur  = JSON.parse(cur_data);
		menu_data_reload();
	}
}
function menu_onresize() {
	mh = parseInt(document.getElementById("menu").offsetHeight);
	uh = parseInt(document.getElementById("menuul").offsetHeight);

	hh = mh - uh;

	if (menu_cur != null)
		menu_cur.style.maxHeight = "" + hh + "px";
}
/***
function menu_reload() {
	console.log("in menu_reload()");
	
	$('#divmenu').load("divmenu", "parts/menu.php", {}, function() {
		m = menu_cur;
		e = entry_cur;

		menu_cur = entry_cur = null;
		if (m) { 
			menu_show(m);
			p = m;

			var o = document.getElementById(p).children;

			// element had content e.innerHTML => must retreive its new instance:
			if (e != null) {
				for (var i = 0; i < o.length; i++) {
					var a = o[i].children[0];
					if (a.innerHTML == e.innerHTML) {
						a.classList.add("current");
						entry_cur = a;
					}
				}
			}
		}
	});
}
***/
function menu_show(id) {
	e = document.getElementById(id);
	if (menu_cur != null) {
		menu_cur.classList.remove("current");
		menu_cur = null;
	}
	menu_cur = e;
	var da = document.getElementById("data_area");
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
	ctrl("menu", data, null, false); 
}
function data_cur_set(data) {
	data_cur = data;
	if (menu_cur != null)
		menu_save({'menu_cur': menu_cur.id, 'entry_cur': entry_cur.id, 'data_cur': JSON.stringify(data)});	
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
	load("data_area", page); 
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
