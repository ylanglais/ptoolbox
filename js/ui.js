
var  UI = function(spec, data, actions) {
	this.spec = spec;
	this.data = data;
	this.act  = actions;
	this.pid  = null;
	this.id   = null;
	this.div  = null;

	this.draw = function(id) {
		this.pid = id;
		var div = document.getElementById(id);
		if (div === undefined) {
			console.log("div '" + id + "' not found");
			return;
		}
		this.div = div;
		uid = document.createElement("div");
		this.id = uid.id = id + "_UI";
		uid.style.backgroundColor = 'lightblue';
		tbl = document.createElement("table");
		tbl.classList.add("form");

		for (e of this.spec) {
			tr = tbl.insertRow();
			th = document.createElement("th");
			th.innerHTML = e.label;
			tr.appendChild(th);
			td = document.createElement("td");
			clas = "";
			if (e.type == "string" || e.type == "numeric") {
				if (this.data != null && this.data.hasOwnProperty(e.property)) {
					def = this.data[e.property];
				} else if ("default" in e) {
					def = e.default;
					clas = "defval";
				} else {
					def = ""; 
				}
				if (e.hasOwnProperty("length")) 

				inp = document.createElement("input");
				inp.type  = "text";
				inp.id    = div.id + "_" + e.property;
				inp.value = def;
				if (clas != "") inp.classList.add(cla);

				inp.addEventListener("click", function(e) { obj.trigger(this.name); });
				td.appendChild(inp);


			} else if (e.type == "bool") {
				if (this.data != null && this.data.hasOwnProperty(e.property) && data[e.property] == true) {
					def = "checked"; 
				} else if ("default" in e && e.default == true) {
					def = "checked";
				} else {
					def = "";
				}
				inp = document.createElement("input");
				inp.type = "checkbox";
				inp.id   = div.id + "_" + e.property;
				inp.checked = def;
				if (clas != "") inp.classList.add(cla);
				td.appendChild(inp);


			} else if (e.type == "list") {
				if (this.data != null && this.data.hasOwnProperty(e.property)) {
					def = this.data[e.property];
				} else if ("default" in e) {
					def = e.default;	
					clas = "defval";
				} else {
					def = "";
				}

				sel = document.createElement("select");
				sel.id   = div.id + "_" + e.property;
				if (clas != "") sel.classList.add(clas);
				td.appendChild(sel);

				for (o of e.values) {
					opt = document.createElement("option");
					if (o == def) {
						s = "selected" ; 
						opt.selected = true
					} else {
						s = "";
					}
					opt.value     = o;
					opt.innerHTML = o;

					sel.appendChild(opt);
					
				}
			} 
			tr.appendChild(td);
				
		}	
		tr = document.createElement("tr");
		td = document.createElement("td");
		td.colSpan = 2;
		td.style.backgroundColor = 'lightyellow';
		for (a of actions) {
			var inp = document.createElement("input");
			inp.type = "button";
			inp.name = a.name;
			obj = this;
			inp.addEventListener("click", function(e) { obj.trigger(this.name); });
			inp.value   = a.label;
			td.appendChild(inp);
			inp = null;
		} 
		tr.appendChild(td);
		tbl.appendChild(tr);
		uid.appendChild(tbl);	
		this.div.appendChild(uid);
		
		return;
	}

	this.collect = function() {
		var data = {};
		if (this.id == null) return null;
		for (e of this.spec) {
			if (e.type == "string" || e.type == "numeric") {
				data[e.property] = document.getElementById(this.div.id + "_" + e.property).value;
			} else if (e.type == "bool") {
				data[e.property] = document.getElementById(this.div.id + "_" + e.property).checked;
			} else if (e.type == "list") {
				s = document.getElementById(this.div.id + "_" + e.property)
				data[e.property] = s.options[s.selectedIndex].text;
			}
		}
		return data;
	}

 	this.trigger = function(name) {
		console.log("this.trigger("+ name + ")");
		if (name == "cancel") {
			this.div.innerHTML = "";
			this.draw(this.pid);
			return;
		}
		if (name == "reset") {
			this.data = null;
			this.div.innerHTML = "";
			this.draw(this.pid);
			return;
		}
		this.data = this.collect();	

		for (a of this.act) if (a.name == name) break;

		if (a.name != name) {
			console.log("err: action " + name + " not found");	
			return;
		}
		console.log("action: " + a.name + ", callback: " + a.callback + ", data: " + JSON.stringify(this.data));
		if (a.hasOwnProperty("callback")) post(a.callback, this.data);
		
		

		//console.log(this.collect());
		// check if action has a cb, collect data and call cb:
	}
	
	// On field value change, update class (required, default)...:
	this.change = function() {
	}
}


