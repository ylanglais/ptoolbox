
function date_dow(d, m, y) {
	// d = parseInt(d);
	// if (d < 10) d = "0" + d;
	// m = parseInt(m);
	// if (m < 10) m = "0" + m;

	date = new Date(y, m-1, d);

	//console.log("dow(" + d + "/" + m + "/"  + y + ") = " + ((sd.getDay() + 6) % 7));
	return (sd.getDay() + 6) % 7;
}

function date_cal_content(par, d, m, y) {
	sd   = new Date(y, m-1, 1);
	ld   = new Date(y, m,   0);

	last_day = ld.getDate();
	if (parseInt(d) < 1)        d = 1;
	if (parseInt(d) > last_day) d = last_day;

	src  = "<table class='minical'><tr>";
	src += "<th onmouseover='this.className=\"minical pre prev\";' onmouseout='this.className=\"minical norm prev\";' onclick='date_month_prev(\"" + par.id + "\");'>&lt;</th>";
	src += "<th class='minical month' colspan='5'>" + m + "/" + y + "</th>";
	src += "<th onmouseover='this.className=\"minical pre next\";' onmouseout='this.className=\"minical norm next\";' onclick='date_month_next(\"" + par.id + "\");'>&gt;</th>";
	src += "</tr>\n";
	src += "<tr class='minical dow'><th class='minical dow'>L</th><th class='minical dow'>M</th><th class='minical dow'>M</th><th class='minical dow'>J</th><th class='minical dow'>V</th><th class='minical dow'>S</th><th class='minical dow'>D</th></tr>\n";
	dd = 1;
	dow = date_dow(d, m, y);

	src += "<tr>";
	if (dow > 0) 
		src += "<th colspan='" + (dow) + "'></th>";

	while (dd <= last_day) {	
		for (i = dow; i < 7; i++) {
			if (dd == d)
				src += "<td class='minical now'>" + dd + "</td>";
			else
				src += "<td class='minical norm' onmouseover='this.className=\"minical pre\";' onmouseout='this.className=\"minical norm\";' onclick='date_cal_choose(\"" + par.id + "\", this);'>" + dd + "</td>";
			dd++;
			if (dd > last_day) { 
				break;
			}
		} 
		dow = 0;
		src +="</tr>\n";
		if (dd < last_day) src+="<tr class='minical'>";
	}	
	src += "</table>\n";	
	return src;
}

function date_month_prev(pid) {
	par = document.getElementById(pid);

	id = par.id + '_date_chooser';
	var dch = document.getElementById(id);
	d = par.value.substr(0, 2);
	m = par.value.substr(3, 2);
	y = par.value.substr(6, 4);

   
	if (m == "01") {
		m = 12;
		y = parseInt(y) - 1;
	} else { 
		m = parseInt(m) - 1;
		if (m < 10) m = "0" + m;
	}

	ld   = new Date(y, m, 0);
	last_day = ld.getDate();
	if (parseInt(d) < 1) d = 1;
	if (parseInt(d) > last_day) d = last_day;

	par.value = d + "/" + m + "/" + y;
	dch.innerHTML = date_cal_content(par, d, m, y);	
}
function date_month_next(pid) {
	par = document.getElementById(pid);
	id = pid + '_date_chooser';
	var dch = document.getElementById(id);
	d = par.value.substr(0, 2);
	m = par.value.substr(3, 2);
	y = par.value.substr(6, 4);


	if (m == "12") {
		m = "01";
		y = parseInt(y) + 1;
	} else { 
		m = parseInt(m) + 1;
		if (m < 10) m = "0" + m;
	}

	ld   = new Date(y, m,  0);
	last_day = ld.getDate();
	if (parseInt(d) < 1) d = 1;
	if (parseInt(d) > last_day) d = last_day;
 
	par.value = d + "/" + m + "/" + y;
	dch.innerHTML = date_cal_content(par, d, m, y);	
}

function date_cal_choose(pid, cell) {
	par = document.getElementById(pid);
	id = pid + '_date_chooser';
	var dch = document.getElementById(id);
	d = parseInt(cell.innerHTML);
	if (d < 10) d = "0" + d;
	
	par.value = d + par.value.substr(2, 8);
	dch.style.display = 'none';
}

function date_cal_open(par) {
	var rect = par.getBoundingClientRect();
	xx = rect.left + 10;
	yy = rect.bottom;
	ww = rect.right - rect.left;

	date = par.value;
	if (date != "") {
		d = date.substr(0, 2);
		m = date.substr(3, 2);
		y = date.substr(6 ,4);
	} else {
		dt = new Date() ;
		d  = dt.getDate();
		m  = dt.getMonth() + 1;
		y  = dt.getFullYear();
		if (d < 10) d = "0" + d;
		if (m < 10) m = "0" + m;
		par.value = d + "/" + m + "/" + y;
	}

	id = par.id + '_date_chooser';

	var dch = document.getElementById(id);
	if (typeof dch === 'undefined' || dch == null) {
		dch = document.createElement('div');	
		dch.id = id;
		dch.className = 'minical'
		document.body.appendChild(dch);
	}
	dch.style.position = 'absolute';
	dch.style.left     = xx + "px";
	dch.style.top      = yy + "px";
	dch.style.display  = 'block';
	dch.innerHTML = date_cal_content(par, d, m, y);
	dch.focus();
	dch.onmouseleave = function() { date_cal_close(par); };
}

function date_cal_update(par) {
	id = par.id + '_date_chooser';
	var dch = document.getElementById(id);
	if (typeof dch !== 'undefined' && dch != null) {
			date = par.value;
		if (date == "") {
			dt = new Date() ;
			d  = dt.getDate();
			m  = dt.getMonth() + 1;
			y  = dt.getFullYear();
		} else {
			d = date.substr(0, 2);
			//d = date.match(/^([0-9]{1,2})\/[0-9]{1,2}\/20[0-9][0-9]$/g);
			if (d < 10) d = "0" + d;
			m = date.substr(3, 2);
			//m = date.match(/^[0-9]{1,2}\/([0-9]{1,2})\/20[0-9][0-9]$/g);
			if (m < 10) d = "0" + m;
			y = date.substr(6 ,4);
			//y = date.match(/^[0-9]{1,2}\/[0-9]{1,2}\/(20[0-9][0-9])$/g);
		}
		dch.innerHTML    = date_cal_content(par, d, m, y);
	}
}

function date_cal_close(par) {
	id = par.id + '_date_chooser';
	var dch = document.getElementById(id);
	if (typeof dch !== 'undefined' && dch != null) {
		dch.style.display = 'none';
	}
}

