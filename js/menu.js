var menu_cur       = null;
var menu_entry_cur = null;

var menu_data_curr = null;

var mh = 0;
var uh = 0;
var hh = 0

window.addEventListener('resize', menu_onresize, true);
window.addEventListener('load',   menu_onresize, true);

function menu_onresize() {
	//console.log("window on resize");
	mh = parseInt(document.getElementById("menu").offsetHeight);
	uh = parseInt(document.getElementById("menuul").offsetHeight);

	hh = mh - uh;

	//console.log("hh = " + hh);
	
	if (menu_cur != null)
		menu_cur.style.maxHeight = "" + hh + "px";
}

function menu_reload() {
	//console.log("in menu_reload()");
	$('#divmenu').load("divmenu", "parts/menu.php", {}, function() {
		m = menu_cur;
		e = menu_entry_cur;

		menu_cur = menu_entry_cur = null;
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
						menu_entry_cur = a;
					}
				}
			}
		}
	});
}

function menu_show(id) {
	e = document.getElementById(id);
	//console.log("menu_show("+id+")");
	if (menu_cur != null) {
		if (menu_cur != null) {
			menu_cur.classList.remove("current");
			menu_cur = null;
		}
		menu_cur = e;
		document.getElementById("data_area").innerHTML = "";
	}

	/* limit max-height: */
	e.style.maxHeight = "" + hh + "px";
	e.classList.add("current");
	menu_cur = e;
}

function menu_onclick(e) {
	//console.log("menu_onclick("+ e.id+ ")");
	if (menu_entry_cur != null) {
		menu_entry_cur.classList.remove("current");
	}
	e.classList.add("current");
	menu_entry_cur = e;
}

function menu_form(fname, titre) {
	menu_data_cur = {"type": "form", "name": fname, "titre": titre};
	ctrl('form', {'fname': fname, 'titre': titre}, 'data_area');
}
function menu_rpt(rpt_name) {
	menu_data_cur = {"type": "rpt", "rpt_name": rpt_name};
	progress('data_area', rpt_name);
	ctrl('rpt', {'rpt_name': rpt_name}, 'data_area');
}
function menu_page(page) {
	menu_data_cur = {"type": "page", "page": page};
	load("data_area", page); 
}
function menu_table(page, datalink) {
	menu_data_cur = {"type": "table", "page": page, "datalink": datalink};
	ctrl("gui", {"page": page, "datalink": datalink}, "data_area"); 
}
function menu_view(page, datalink) {
	menu_data_cur = {"type": "view", "page": page, "datalink": datalink};
	ctrl("gui", {"page": page, "type": "view", "datalink": datalink}, "data_area"); 
}
function menu_data_reload() {
	switch (menu_data_cur.type) {
	case 'form':
		menu_form(menu_data_cur.fname, menu_data_cur.titre);
		break;
	case 'rpt':
		menu_rpt(menu_data_cur.rptname);
		break;
	case 'page':
		menu_page(menu_data_cur.page)
		break;
	case 'table':
		menu_table(menu_data_cur.page, menu_data_cur.datalink)
		break;
	case 'view':
		menu_view(menu_data_cur.page, menu_data_cur.datalink)
		break;
	}
}
