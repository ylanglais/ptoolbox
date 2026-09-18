function parse_params(data, params) {
	for (i in params) {
		//dbg(i);
		if (params[i].type == 'date') {
			var v = el(i).value;
			var val = v.substring(6,10) + "-" + v.substring(3,5) + "-" + v.substring(0,2);
			data[i] = val;
		} else if (params[i].type == 'list' || params[i].type == 'mlist') {
			var r = [];
			var v = el(i);
			//dbg(i + " + " + JSON.stringify(v));
			for (let o of v.options) {	
				//dbg("text: " + o.text + " value: "+ o.value);
				if (o.selected) {
					r.push(o.text);
				}
			}
			if (r.length == 1) data[i] = r[0];
			else data[i] = r;
			//dbg(i + " --> " + data[i]);
		} else {
			data[i] = el(i).value;
		}
		//dbg("data[" + i + "]: " + data[i]);
	}
}
function form_load(name, form, action) {
 	form = atob(form);
	form = JSON.parse(form);
	let data = {};
	data["fname"] = form.fname;
	data["form"]   = true;
	data["action"] = action;

	if (form.param_groups != []) {
		for (j in form.param_groups) 
			parse_params(data, form.param_groups[j]);
	}

	//dbg(data);
	
	progress('form_result', form.fname);
	ctrl("form", data, 'form_result');
}
function form_download(name, form, action) {
 	form = atob(form);
	form = JSON.parse(form);
	let data = {};
	data["fname"] = form.fname;
	data["form"]   = true;
	data["action"] = action;

	if (form.param_groups != []) {
		for (j in form.param_groups) 
			parse_params(data, form.param_groups[j]);
	}

	//dbg(data);
	ctrl("form", data, null, false, true);
}
