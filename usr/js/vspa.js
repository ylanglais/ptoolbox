let vspa_dropdown = null;

function vspa_dd_new(inp) {
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
	inp.listenr = inp.addEventListener("keydown", vspa_key);

	inp.addEventListener('change', function() { vspa_onsel(inp, null); });

	sel = document.createElement("select");
	sel.id = inp.id + "_dds";
	sel.classList.add("datalist");
	sel.size = 20;
	sel.style.zIndex = 100;
	div.appendChild(sel);
	return sel;
}
function vspa_dd_destroy(inp) {
//	inp.removeEventListener("keydown", vspa_key);
	if (vspa_dropdown != null) vspa_dropdown.remove();
	vspa_dropdown = null;
}
function vspa_onsel(inp, sel) {
	if (sel == null) {
		sel = vspa_dropdown;
		if (sel == null) return
	}	
	v = sel.options[sel.selectedIndex].value;
	inp.value = v;
	vspa_dd_destroy(inp);
	event.stopPropagation();
	inp.value = v;
}

function vspa_search_input()  {
	e = document.getElementById("vspa_search");
	if (e !== null && e.value.length > 0) {
		if (vspa_dropdown == null) 
			vspa_dropdown = vspa_dd_new(e);
		sel = vspa_dropdown;
		//console.log("input: " + e.value);
		lst = ctrl("vspa", { "input": e.value });
		//console.log(lst);
		sel.inputid   = e.id;	
		sel.innerHTML = "";
		s = "";
		i = 1;
		if (lst !== null) for (o of lst) {
			str = "<option value=\"" + o + "\">" + o  + "</option>";
			s += str;
			i++;
		}
		sel.innerHTML = s;
		sel.innerHTML = s;
		if (lst !== null && lst.length == 1) { 
			sel.options[0].selected = true;
		sel.selectedOption = 0;
		}
		sel.size = i;
	}
}
function vspa_search() {
	e = document.getElementById("vspa_search");
	console.log("phrase: " + e.value);
	ctrl("vspa", { "qry": e.value }, "vspa_result");
}
function vspa_detail(id) {
	console.log("id: " + id);
	ctrl("vspa", { "partid": id }, "vspa_detail");
}
function vspa_key(e) {
	inp = this;
	if (!inp) return;
	if (vspa_dropdown === null) {
		if (e.keyCode = 13) {
			vspa_search();
		}
		return;
	}
	sel = vspa_dropdown;
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
console.log(">>> " + sel.options[sel.selectedIndex].value);

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
console.log(">>> " + sel.options[sel.selectedIndex].value);
		event.stopPropagation();
	} else if (e.keyCode == 13) {
		// Return:
		if (sel.selectedIndex >= 0) {
console.log(">>> " + sel.options[sel.selectedIndex].value);
			inp.value = sel.options[sel.selectedIndex].value;
		}
		event.stopPropagation();
		vspa_dd_destroy(inp);
		vspa_search();
	} else if (e.keyCode == 27) {
		// Escape:
		vspa_dd_destroy(inp);
		event.stopPropagation();
	} 
}
