function form_load(name, form, action) {
 	form = atob(form);
	form = JSON.parse(form);
	namedata = name + "_data";
	let data = {};
	data["form"]   = true;
	data["action"] = action;
	for (i in form.params) {
		if (form.params[i].type == 'date') {
			v = document.getElementById(i).value;
			val = v.substring(6,10) + "-" + v.substring(3,5) + "-" + v.substring(0,2);
			data[i] = val;
		} else if (form.params[i].type == 'list') {
			v = document.getElementById(i);
			data[i] = v.options[v.selectedIndex].text;
			console.log(i + " --> " + data[i]);
		} else {
			data[i] = document.getElementById(i).value;
		}
	}	
	console.log(">>> " + JSON.stringify(data));
	$("#"+namedata).load(form.url, data);
}


