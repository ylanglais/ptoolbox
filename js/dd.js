function dd_drw(prnt) {
	if (prnt === null) {
		err("not element with id \"" +  id +"\"");
		return null;
	}
	if (prnt.hasOwnProperty("dd") && prnt.dd !== null) {
		return prnt.dd;
	}

	prnt.addEventListener('blur', function() { dd_destroy(prnt); });
	// get input dimension;
	rct = prnt.getBoundingClientRect();
	div = document.createElement("div");
	// create a div of dropdown class:
	div.id             = prnt.id + "_dd";
	div.pid            = prnt.id;
	div.classList.add("dropdown"),
	div.style.position = "fixed", 
	div.style.left     = Math.round(rct.left)   + "px";
	div.style.top      = Math.round(rct.bottom) + "px";
	div.display        = "block";
	
	prnt.parentElement.appendChild(div);

	dd = document.createElement("select");
	dd.id           = e.id + "_dds";
	dd.pid          = div.id;
	dd.prnt         = div;
	dd.size         = 20;
	dd.style.zIndex = 100;
	dd.classList.add("datalist");
	div.appendChild(dd);

	// set the onsel dd callback:
	prnt.addEventListener('change', function() { dd_onsel(prnt); });

	prnt.dd = dd;
	return dd;
}
function dd_new(prnt) {
	if (prnt === null) {
		err("not element with id \"" +  id +"\"");
		return null;
	}
	if (prnt.tagName != "INPUT") {
		err("element " + id + " is not an input but a " + prnt.tagName);
		return null;
	}
	if (prnt.type != "text") {
		err("element " + id + " is not a text input but a " + prnt.type); 
	}

	// Set the keydown callback:
	prnt.listenr   = prnt.addEventListener("keydown", dd_onkey);
}
function dd_destroy(prnt) {
	dd = prnt.dd;
	if (dd !== null) dd.remove();
	prnt.dd = null;
}
function dd_onsel(prnt) {
	if (prnt.dd === undefined) return;
	if (prnt.dd === null) return;
	if (prnt.dd.tagName != "SELECT") {
		err(prnt.dd.id + " is not a SELECT but a "  + prnt.dd.tagName);
		return;
	}
	let v = prnt.dd.options[prnt.dd.selectedIndex].value;
	try {
		j = JSON.parse(v);
	} catch (error) {
		j = v;
	}
	if (typeof j === 'object') {
		prnt.value = j.key;
		prnt.alt   = j.linkedval;
	} else {
		prnt.value = j;
	}

	dd_destroy(prnt);
	event.stopPropagation();
}
function dd_input_complete(prnt, lst) {
	dd = prnt.dd;
	dd.innerHTML = "";
	s = "";
	i = 1;
	if (lst !== null) for (o of lst) {
		if (typeof o === "string") {
			str = "<option value=\"" + o + "\">" + o  + "</option>";
		} else if (typeof o === "object" && o.hasOwnProperty("key") && o.hasOwnProperty("val")) {
			if (o.hasOwnProperty("linkedval")) {
				str = "<option value='{\"key\": \""+o.key+"\", \"linkedval\": \"" + o.linkedval + "\"}'>" + o.val + "</option>";
			} else {
				str = "<option value=\"" + o.key + "\"" + lv + ">" + o.val + "</option>";
			}
		} else continue;
		s += str;
		i++;
	}
	dd.innerHTML = s;
	if (lst !== null && lst.length == 1) { 
		dd.options[0].selected = true;
		dd.selectedOption = 0;
	}
	dd.size = i;
}
function dd_complete(prntid, controler, complete_ctrl, complete_qry, limit) {
	//dbgp();	
	if ((prnt = el(prntid))  === null) return;
	if (limit === null) limit = 3;

	if (prnt.value.length < limit) return;

	if (prnt !== null) {
		dd_new(prnt);
		dd_drw(prnt);
	}
	var data = {};
	data["complete_ctrl"] = complete_ctrl;
	data[complete_qry]    = prnt.value;
	lst = ctrl(controler, data);
	dd_input_complete(prnt, lst);
}
function dd_onkey(e) {
	if ((prnt = this) === null) return;
	if (prnt.dd === undefined || prnt.dd === null) {
		// keycode 13 = 0xOD == Enter:
		if (e.keyCode = 13) {
			dd_destroy(prnt);
		}
		return;
	}
	if (e.keyCode == 40) {
		// Down:
		if (prnt.dd.selectedIndex < 0) {
			prnt.dd.options[0].selected                          = true;
			prnt.dd.selectedIndex                                = 0;
		} else if (prnt.dd.selectedIndex < prnt.dd.options.length - 1) {
			prnt.dd.options[prnt.dd.selectedIndex++].selected    = false;
			prnt.dd.options[prnt.dd.selectedIndex].selected      = true;
		}

		event.stopPropagation();
	} else if (e.keyCode == 38) {
		// Up+ id +  "\", \"" +:
		if (prnt.dd.selectedIndex < 0) {
			prnt.dd.options[prnt.dd.options.length - 1].selected = true;
			prnt.dd.selectedIndex = 0;
		} else if (prnt.dd.selectedIndex > 0) {
			prnt.dd.options[prnt.dd.selectedIndex--].selected    = false;
			prnt.dd.options[prnt.dd.selectedIndex].selected      = true;
		}
		event.stopPropagation();
	} else if (e.keyCode == 13) {
		// Return:
		if (prnt.dd.selectedIndex >= 0) {
			let v = prnt.dd.options[prnt.dd.selectedIndex].value;
			try {
				j = JSON.parse(v);
			} catch (error) {
				j = v;
			}
			if (typeof j === 'object') {
				prnt.value = j.key;
				prnt.alt   = j.linkedval;
			} else {
				prnt.value = prnt.dd.options[prnt.dd.selectedIndex].value;
			}
		}
		event.stopPropagation();
		dd_destroy(prnt);
	} else if (e.keyCode == 9) {
		// Tab:
		event.stopPropagation();
		dd_destroy(prnt);
	} else if (e.keyCode == 27) {
		// Escape:
		event.stopPropagation();
		dd_destroy(prnt);
	} 
}
