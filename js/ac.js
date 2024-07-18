function ac_new(inp) {
	rct = inp.getBoundingClientRect();
	
	div = document.createElement("div");
	div.id             = inp.id + "_dd";
	div.classList.add  = "dropdown",
	div.style.position = "fixed",
	div.style.left     = Math.round(rct.left)   + "px";
	div.style.top      = Math.round(rct.bottom) + "px";
	div.display        = "visible";

	inp.parentElement.appendChild(div);

	inp.listenr = inp.addEventListener("keydown", ac_key);

	sel = document.createElement("select");
	sel.id = inp.id + "_dds";
	sel.classList.add("datalist");
	sel.size = 20;
	// remove sel if out of focus... not that nice.
	//sel.addEventListener('mouseout',  function () { ac_destroy(inp)});
	
	div.appendChild(sel);
	return sel;
}
function ac_destroy(inp) {
	inp.removeEventListener("keydown", ac_key);
	if ((sel = document.getElementById(inp.id + "_dd")) != null)
		sel.remove();

}
function ac_key(e) {
	inp = this;
	if (!inp) return;
	if (document.getElementById(inp.id + "_dds") == null) {
		return;
	}
	//var e = window.event;
console.log(">> " + e.keyCode);
	if (e.keyCode == 40) {
		// Down:
		if (sel.selectedIndex < 0) {
			sel.options[0].selected                      = true;
			sel.selectedIndex                            = 0;
		} else if (sel.selectedIndex < sel.options.length - 1) {
			sel.options[sel.selectedIndex++].selected    = false;
			sel.options[sel.selectedIndex].selected      = true;
		}
		event.stopPropagation();
	} else if (e.keyCode == 38) {
		// Up:
		if (sel.selectedIndex < 0) {
			sel.options[sel.options.length - 1].selected = true;
			sel.selectedIndex = 0;
		} else if (sel.selectedIndex > 0) {
			sel.options[sel.selectedIndex--].selected    = false;
			sel.options[sel.selectedIndex].selected      = true;
		}
		event.stopPropagation();
	} else if (e.keyCode == 13) {
		// Return:
		if (sel.selectedIndex < 0) {
			ac_destroy(inp);
			return;
		}
		inp.value = sel.options[sel.selectedIndex].value;
		event.stopPropagation();
		ac_destroy(inp);
	} else if (e.keyCode == 27) {
		// Escape:
		ac_destroy(inp);
	} 
}
function ac_change(inp, pdata, fld) {
	if (document.getElementById(inp.id + "_dds") == null) {
		sel = ac_new(inp);
	} else {
		sel  = document.getElementById(inp.id + "_dds");
	}
	opts = ctrl("prov", {"prov_data": pdata, "action": "fdata", "field": fld, "str": inp.value});

	if (opts.length < 1) {
		ac_destroy(inp);
		return;
	}

	sel.inputid   = inp.id;	
	sel.innerHTML = "";
	s = "";
	i = 1;
	for (o of opts) {
		str = "<option onclick='ac_onsel(\""+inp.id+"\", \""+ o +"\");' value='" + o + "'>" + o  + "</option>";
		s += str;
		i++;
	}
	sel.innerHTML = s;
	if (opts.length == 1) { 
		sel.options[0].selected = true;
		sel.selectedOption = 0;
	}
	sel.size = i;
}
function ac_onsel(id, v) {
	inp = document.getElementById(id);
	inp.value = v;
	ac_destroy(inp);
}
