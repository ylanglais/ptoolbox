function glist_popup(prov) {
	//console.log("glist_popup("+prov+")")
	event.stopPropagation();
	popup_ctrl("glist_popup", "Sélection de champs", "glist", {"glist_popup": prov});
	return false;
}
function glist_go(id, pdat, opts, start, lines) {
	//console.log(">>>> " + JSON.stringify(opts));
	if (start != null) opts.start = start ;
	if (lines != null) opts.page  = lines ;
	ctrl("glist", {"prov": pdat, "opts": opts}, id);
}
function glist_sort(el, pdat, opts, field) {
	event.stopPropagation();
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
	ctrl("glist", {"prov": pdat, "opts": opts, "save_opts": true}, opts.id);
	return false;
}
function glist_view(id, data) {
	ctrl("gform", { "id": id, "data": data }, id);
}
function glist_toggle_selected(classname) {
	event.stopPropagation();
	for (const e of document.getElementsByClassName(classname)) {
		e.checked = !e.checked;
	}
}
function glist_action() {
}
