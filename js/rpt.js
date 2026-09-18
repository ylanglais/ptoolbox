function rpt_reload(id) {
	let data = {};
	if ((v = el('rpt_vars')) != null) {
		data.rpt_vars = JSON.parse(v.value);
	}
	if ((r = el('rpt_name')) != null) data.rpt_name = r.value;
	if ((f = el('rpt_form')) != null) data.rpt_form = JSON.parse(f.value);
	if (id == null) id = "data_area";

	if (el("rpt_body") == null) tgt = 'data_area'; 
	else tgt = 'rpt_body';

	progress(tgt,  data.rpt_name);

	var div = el('form_div');

	if (f != null ) {
	for (j of data.rpt_form) for (i in j) {
			if (j[i].type == 'date') {
				var v = el(i).value;
				var val = v.substring(6,10) + "-" + v.substring(3,5) + "-" + v.substring(0,2);
				data.rpt_vars[i] = val;
			} else if (j[i].type == 'list' || j[i].type == 'mlist') {
				var r = [];
				var v = el(i);
				for (let o of v.options) {	
					if (o.selected) {
						r.push(o.text);
					}
				}
				if (r.length == 1) data.rpt_vars[i] = r[0];
				else data.rpt_vars[i] = r;
			} else {
				data.rpt_vars[i] = el(i).value;
			}
		}
	}

	ctrl("rpt", data, id);
}
