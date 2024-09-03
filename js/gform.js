function gform_changed(id) {
	var ori = JSON.parse(document.getElementById("__ori_" + id).value);
	var dat = gform_data(id);
}
function gform_data(id) {
	var data = {};
	const div = document.getElementById("__gform_" + id);
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
function gform_action(id, action) {
	var gfdat = gform_data(id);
	//var req   = JSON.parse(document.getElementById("__req__").value);
	var ori   = JSON.parse(document.getElementById("__ori_" + id).value);
	let sopt = document.getElementById("__opts_" + id).value;
	let opts = JSON.parse(sopt);
	let pdat = glist_pdat(opts.parentid);

	var data  = { "data": gfdat, "prov": pdat, "ori": ori };
	ctrl("gform", {"data": data, "action": action}, "__gform_" + id);
	glist_go(opts.parentid, opts.start, opts.lines); 
}
