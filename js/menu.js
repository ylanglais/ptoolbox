var menu_cur       = null;
var menu_sub_cur   = null;
var menu_entry_cur = null;

var mh = 0; 
var mu = 0; 

window.addEventListener('resize', menu_onresize, true);

function menu_onresize() {
	//console.log("window on resize");
	if (menu_cur == null) return;
	mh = parseInt($("#menu").height());
		/* Compute max height for the submenu ul: */
	h = mh - uh;
	//console.log("resize: new max-height = " + h + "px");
	$('#'+ menu_cur).css("max-height", "" + h + "px");
}

function menu_reload() {
	//console.log("in menu_reload()");
	$('#divmenu').load("divmenu", "parts/menu.php", {}, function() {
		m = menu_cur;
		s = menu_sub_cur;
		e = menu_entry_cur;

		menu_cur = menu_sub_cur = menu_entry_cur = null;

		if (m) { 
			menu_show(m);
			p = m;
			if (s) { 
				menu_sub_show(s);
				p = s;
			}

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
	//console.log("menu_show("+id+")");
	if (menu_cur != null) {
		if (menu_sub_cur != null) {
			$('#' + menu_sub_cur).hide();
			menu_sub_cur = null;
		}
		$('#' + menu_cur).hide();
		document.getElementById("data_area").innerHTML = "";
	}

	/* Compute height of menu ul: */
	mh = parseInt($("#menu").height());
	uh = parseInt($("#menuul").height());

	/* Compute max height for the submenu ul: */
	h = mh - uh;
	
	/* limit max-height: */
	$('#'+ id).css("max-height", "" + h + "px");
	$('#'+ id).show(100);
	$('#'+ id).height(h + "px");
	menu_cur = id;
}

function menu_sub_show(id) {
	//console.log("menu_sub_show("+id+")");
	if (menu_sub_cur != null) {
		$('#' + menu_sub_cur).hide();
	}

	$('#'+ id).show(100);
	menu_sub_cur = id;
}

function menu_onclick(el) {
	//console.log("menu_onclick("+el.innerHTML+")");
	if (menu_entry_cur != null) {
		$(menu_entry_cur).removeClass("current");
	}
	$(el).addClass("current");
	menu_entry_cur = el;
}

/*
function menu_get_data(what) {
	let fromdate = document.getElementById('fromdate').value;
	let todate   = document.getElementById('todate').value;
	load("data_area", what, {'fromdate': $("#fromdate").val(), 'todate': $("#todate").val()});
}
function menu_hdr(module, titre) {
	load("data_area", "tdb.php", {'module': module, 'titre': titre});
}
*/
function menu_form(fname, titre) {
	load("data_area", "ctrl.php", {'ctrl': 'form', 'fname': fname, 'titre': titre});
}
function menu_rpt(rptname) {
	var r = sync_post("ctrl.php", {"ctrl": "stats", "stats_key": rptname});
	if (r == false) {
		document.getElementById("data_area").innerHTML = '<div id="a_' + rptname + '" class="data_modal"><div class="data_modal_hourglass"><img src="images/wait.gif" width="100px"/><br/>Computing...</div></div>';
	} else {
		document.getElementById("data_area").innerHTML = '<div id="a_' + rptname + '" class="data_modal"><div class="data_modal_hourglass"><progress id="tt" max="100" value="0"></progress><br/>Computing...</div></div>';
		step = r.max * 10.;
		inter = setInterval(function() { 
			e = document.getElementById('tt');
			if (!e) clearInterval(inter);
			else {
				e.value += 1; 
				e.innerHTML = "" + e.value + "%";
				if (e.value == 100) clearInterval(inter);
			}
		}, step);
	}
	load("data_area", "ctrl.php", {'ctrl': 'rpt', 'rptname': rptname});
}
function menu_page(page) {
	load("data_area", page); 
}
function menu_table(page, datalink) {
	load("data_area", "ctrl.php", {"ctrl": "gui", "page": page, "datalink": datalink}); 
}
function menu_view(page, datalink) {
	load("data_area", "ctrl.php", {"ctrl": "gui", "page": page, "type": "entity", "datalink": datalink}); 
}

