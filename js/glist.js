function glist_go(id, pdat, opts, start, lines) {
	//console.log(">>>> " + JSON.stringify(opts));
	if (start != null) opts.start = start ;
	if (lines != null) opts.page  = lines ;
	
	data = { "ctrl": "glist", "prov": pdat, "opts": opts};
	//console.log("----> glist_go("+ id + "," + JSON.stringify(data));
	load(id, "ctrl.php", data);
}
function glist_sort(el, pdat, opts, field) {
	f = opts.sort;
	o = opts.order;
	//console.log("new: "+ field, ", old: sortfield: " + f + ", order: " + o);
	
	if (f == field) {
		if (o == 'up') {
			el.classList.remove('up');
			el.classList.add('down');
			o = 'down';
		} else {
			el.classList.remove('down');
			el.classList.add('up');
			o = 'up';
		}
	} else {
		if (f !== false) {
			oe = document.getElementById("sort_" + f);
			oe.classList.remove('sel');
		}	
		el.classList.add('sel');
		o = 'up';
		if (el.classList.contains('down')) o = 'down';
	}
	opts.sort  = field;
	opts.order = o;
	data = { "ctrl": "glist", "prov": pdat, "opts": opts};
	load(opts.id, "ctrl.php", data);
}
function glist_view(id, data) {
	d = { "ctrl": "gform", "id": id, "data": data };
	load(id, "ctrl.php", d);
	//load(data.opts.id, "ctrl.php", data.opts);

}
function glist_toggle_selected(classname) {
	for (const e of document.getElementsByClassName(classname)) {
		e.checked = !e.checked;
	}
}
function glist_action() {
}
