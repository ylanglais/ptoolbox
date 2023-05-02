function gform_data(id) {
	var data = {};
	const div = document.getElementById(id);
	if (div == null) return {};
	var inps = div.getElementsByTagName('input');
	for (i of inps) {
		// console.log("input type: " + i.type + ", id: " + i.id + ", name: " + i.name + ", value: " + i.value + ", checked: " + i.checked);
		if (i.type == "button") continue;
		if (i.type == "submit") continue;
		if (i.type == "hidden") continue;
		if (i.type == "checkbox") {
			data[i.id] = i.checked;
		} else if (i.type == "radio") {
			if (i.checked == true) {
				data[i.name] = i.value;
			}
		} else {
			data[i.id] = i.value;
		}
	}
	// Select
	inps = div.getElementsByTagName('select');
	for (i of inps) {
		//console.log("i.options[" + i.selectedIndex + "].value: "+ i.options[i.selectedIndex].value );
		data[i.id] = i.options[i.selectedIndex].value;
		if (data[i.id] == 'null' || data[i.id] == '') data[i.id] = null;
	}
	// textarea
	inps = div.getElementsByTagName('textarea');
	for (i of inps) {
		data[i.id] = i.value;
	}
	// return data
	return data;
}
function gform_action(id, pdata, action) {
	var gfdat = gform_data(id);
	//var req   = JSON.parse(document.getElementById("__req__").value);
	var ori   = JSON.parse(document.getElementById("__ori__").value);
	var data  = { "data": gfdat, "prov": pdata, "ori": ori };
	var ctrl = { "ctrl": "gform", "data": data, "action": action};
	load(id, "ctrl.php", ctrl);
	let sopt = document.getElementById("opts").value;
	let opts = JSON.parse(sopt);
	glist_go(opts.parentid, pdata, opts, opts.start, opts.lines); 
}
