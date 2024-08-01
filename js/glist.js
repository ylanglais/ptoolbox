function glist_opts(id) {
	_el = document.getElementById(id + "_opts");
	if (_el == null) return _el;
	return JSON.parse(_el.value);
}
function glist_pdat(id) {
	_el = document.getElementById(id + "_pdat");
	if (_el == null) return el;
	return _el.value;
}
function glist_popup(id) {
	//console.log("glist_popup("+prov+")")
	event.stopPropagation();
	popup_ctrl("glist_popup", "Sélection de champs", "glist", {"glist_popup": glist_pdat(id)});
	return false;
}
function glist_go(id, start, lines) {
	pdat = glist_pdat(id);	
	opts = glist_opts(id);	
	if (start != null) opts.start = start ;
	if (lines != null) opts.page  = lines ;
	ctrl("glist", {"prov": pdat, "opts": opts}, id);
}
function glist_sort(el, id, field) {
	event.stopPropagation();

	pdat = glist_pdat(id);	
	opts = glist_opts(id);	

	if (opts.sort == field) {
		if (opts.order == 'up') {
			el.classList.remove('up');
			el.classList.add('down');
			opts.order = 'down';
		} else {
			el.classList.remove('down');
			el.classList.add('up');
			opts.order = 'up';
		}
	} else {
		if (opts.sort !== false) {
			oe = document.getElementById("sort_" + opts.sort);
			oe.classList.remove('sel');
		}	
		el.classList.add('sel');
		opts.order = 'up';
		if (el.classList.contains('down')) opts.order = 'down';
	}
	opts.sort  = field;
	ctrl("glist", {"prov": pdat, "opts": opts, "save_opts": true}, id);
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
function glist_field_filter(e, id, fld) {
	event.stopPropagation();
	if (document.getElementById('inp_'+fld) != null) return;
	d = e.parentElement;
	pdat = glist_pdat(id);

	div = document.createElement("div");
	div.id = "ac_" + fld;

	d.appendChild(div);

	opts = glist_opts(id);
	try {
		val = opts.filter[fld];
		// clean img error emulating div onload:
		if ((ttt = document.getElementById("onc_ac_" + fld)) != null) ttt.remove();
	} catch(e) {
		val = null;
	}

	
	
	inp = ac_new(id, pdat, fld, val, glist_filter_add);


	div.appendChild(inp);

	img = document.createElement("img");
	img.setAttribute('src', 'images/close.white.png');
	img.setAttribute('width', '15px');
	img.style.verticalAlign = 'baseline';
	img.addEventListener('click', function(event) {event.stopPropagation(); glist_filter_field_rm(id, div, fld); });

	div.appendChild(img);

	inp.focus();
}
function glist_filter_add(id, fld, value) {
	opts = glist_opts(id);
	pdat = glist_pdat(id);

	if (opts.filter == null) opts.filter = {};
console.log("glist_filter_add --> " + value);
	opts.filter[fld] = value;
	ctrl("glist", {"prov": pdat, "opts": opts, "save_opts": true}, opts.id);
}
function glist_fdata_list(e, id, fld) {
	pdat = glist_pdat(id);
	fdata = ctrl("glist", {"fdata":fld, "prov": pdat});
	e.backup = e;
}
function glist_filter_field_rm(id, el, fld) {
	opts = glist_opts(id);
	pdat = glist_pdat(id);

	delete opts.filter[fld];

	ctrl("glist", {"prov": pdat, "opts": opts, "save_opts": true}, opts.id);
	el.parentElement.remove();
}
function glist_fdata_list(e, id, fld) {
	pdat = glist_pdat(id);
	fdata = ctrl("glist", {"fdata":fld, "prov": pdat});
	e.backup = e;
}

