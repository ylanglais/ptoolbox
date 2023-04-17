function gform_data(id) {
	var data = {};
	const div = document.getElementById(id);
	if (div == null) return {};
	var inps = div.getElementsByTagName('input');
	for (i of inps) {
		if (i.type == "button") continue;
		if (i.type == "submit") continue;
		if (i.type == "checkbox") {
			if (i.checked == true) {
				data[i.id] = true;
			} else {
				data[i.id] = false;
			}
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
		data[i.id] = i.option[i.selected];
	}
	// textarea
	inps = div.getElementsByTagName('textarea');
	for (i of inps) {
		data[i.id] = i.values;
	}
	// return data
	return data;
}
function gform_action(id, pdata, action) {
	var gfdat = gform_data(id);
	var data = { "ctrl": "gform", "data": { "data": gfdat, "prov": pdata }, "action": action};
	console.log(JSON.stringify(data));
	load(id, "ctrl.php", data);
}
