function form_load(name, form, action) {
	form = atob(form);
	form = JSON.parse(form);
	namedata = name + "_data";
	let data = {};
	data["form"]   = true;
	data["action"] = action;
	for (i in form.params) {
		if (form.params[i].type == 'date') {
			var v = document.getElementById(i).value;
			var val = v.substring(6,10) + "-" + v.substring(3,5) + "-" + v.substring(0,2);
			data[i] = val;
		} else if (form.params[i].type == 'list' || form.params[i].type == 'mlist') {
			var r = [];
			var v = document.getElementById(i);
			//console.log(i + " + " + JSON.stringify(v));
			for (let o of v.options) {	
				//console.log("text: " + o.text + " value: "+ o.value);
				if (o.selected) {
					r.push(o.text);
				}
			}
			if (r.length == 1) data[i] = r[0];
			else data[i] = r;
			//console.log(i + " --> " + data[i]);
		} else {
			data[i] = document.getElementById(i).value;
		}
	}	
	//console.log(">>>call " + form.url + " with " + JSON.stringify(data)); 
	$("#"+namedata).load(form.url, data);
}
