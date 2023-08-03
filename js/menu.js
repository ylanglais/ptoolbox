var menu_cur       = null;
var menu_entry_cur = null;

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
	load("data_area", "ctrl.php", {'ctrl': 'form', 'fname': fname, 'titre': titre});
}
function menu_rpt(rptname) {
	progress('data_area', rptname);
	load("data_area", "ctrl.php", {'ctrl': 'rpt', 'rptname': rptname});
}
function menu_page(page) {
	load("data_area", page); 
}
function menu_table(page, datalink) {
	load("data_area", "ctrl.php", {"ctrl": "gui", "page": page, "datalink": datalink}); 
}
function menu_view(page, datalink) {
	load("data_area", "ctrl.php", {"ctrl": "gui", "page": page, "type": "view", "datalink": datalink}); 
}

