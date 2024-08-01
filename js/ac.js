function ac_new(id, pdat, fld, value, on_change) {
	if (document.getElementById('ac_inp_'+fld) != null) return;


	inp = document.createElement("input");
	inp.setAttribute("type", "text");

	inp.id = "ac_inp_" + fld;

	inp.addEventListener('click', event => event.stopPropagation());	
	//inp.addEventListener('mouseout', event => event.stopPropagation());	
	inp.addEventListener('input',  function() { ac_input(inp);  });
	inp.addEventListener('change', function() { ac_onsel(inp, null); ac_change(inp); });

	inp.extid = id;
	inp.pdat  = pdat;
	inp.fld   = fld;
	inp.on_change = on_change;

	if (value != null) inp.value = value;

	return inp;
}
function ac_value_set(inp, val) {
	inp.value = val;	
}
function ac_destroy(div) {
	inp = document.getElementById("ac_" + fld);
	if (inp == null) return;

	inp.on_destroy(id, fld, value);
	
	if (e != null) e.remove();
}
function ac_change(inp) {
	
	console.log("inp.value = " + inp.value);	
	inp.on_change(inp.extid, inp.fld, inp.value);
}
function ac_sel_new(inp) {
	rct = inp.getBoundingClientRect();
	
	div = document.createElement("div");
	div.id             = inp.id + "_dd";
	div.classList.add  = "dropdown",
	//div.style.position = "absolute",
	div.style.position = "fixed",
	div.style.left     = Math.round(rct.left)   + "px";
	div.style.top      = Math.round(rct.bottom) + "px";
	div.display        = "block";

	inp.parentElement.appendChild(div);
	inp.listenr = inp.addEventListener("keydown", ac_key);

	sel = document.createElement("select");
	sel.id = inp.id + "_dds";
	sel.classList.add("datalist");
	sel.size = 20;
	sel.style.zIndex = 100;

	//sel.addEventListener('select', function(){console.log("select"); ac_onsel(inp, sel)});
	//sel.addEventListener('change', function(){console.log("change"); ac_onsel(inp, sel)});

	div.appendChild(sel);
	return sel;
}
function ac_sel_destroy(inp) {
	inp.removeEventListener("keydown", ac_key);
	if ((sel = document.getElementById(inp.id + "_dd")) != null)
		sel.remove();
}
function ac_click(el, ev) {
	console.log("ac_click at: " + ev.clientX + ", " + ev.clientY);
	ev.stopPropagation();
}
function ac_key(e) {
	inp = this;
	if (!inp) return;
	if (document.getElementById(inp.id + "_dds") == null) {
		return;
	}
	// console.log(">> " + e.keyCode);
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
		// Up+ id +  "\", \"" +:
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
		//inp.classList.Addor REMOVE();
		if (sel.selectedIndex < 0) {
			ac_sel_destroy(inp);
			event.stopPropagation();
			inp.dispatchEvent(new Event("change"));
			return;
		}
		inp.value = sel.options[sel.selectedIndex].value;
		event.stopPropagation();
		ac_sel_destroy(inp);
	} else if (e.keyCode == 27) {
		// Escape:
		ac_sel_destroy(inp);
	} 
}

function ac_input(inp) {
	if (document.getElementById(inp.id + "_dds") == null) {
		sel = ac_sel_new(inp);
	} else {
		sel  = document.getElementById(inp.id + "_dds");
	}
	opts = ctrl("prov", {"prov_data": inp.pdat, "action": "fdata", "field": inp.fld, "str": inp.value});

	if (opts.length < 1) {
		ac_sel_destroy(inp);
		return;
	}

	sel.inputid   = inp.id;	
	sel.innerHTML = "";
	s = "";
	i = 1;
	for (o of opts) {
		str = "<option value='" + o + "'>" + o  + "</option>";
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
function ac_onsel(inp, sel) {
console.log("onsel");
	if (sel == null) {
		sel = document.getElementById(inp.id + "_dds");
		if (sel == null) return
	}	
	v = sel.options[sel.selectedIndex].value;
	inp.value = v;
console.log("inp.value: " + inp.value + ", v = " + v);
	ac_sel_destroy(inp);
	event.stopPropagation();
	inp.value = v;
}
function ac_destroy(fld) {
	e = document.getElementById("acflt_" + fld);
	if (e != null) e.remove();
}
