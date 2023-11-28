function geom_inner_width() {
	if (window.innerWidth) {
		return window.innerWidth;
	} else if (document.body && document.body.offsetWidth) {
		return document.body && document.body.offsetWidth;
	} else if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
		return document.documentElement.offsetWidth;
	} else {
		$(window).width();
	}
}

function geom_inner_height() {
	if (window.innerHeight) {
		return window.innerHeight;
	} else if (document.body && document.body.offsetHeight) {
		return document.body && document.body.offsetHeight;
	} else if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetHeight ) {
		return document.documentElement.offsetHeight;
	} else {
		$(window).height();
	}
}

function geom_sb_width() {
	var inner = document.createElement('p');
	inner.style.width = "100%";
	inner.style.height = "200px";

	var outer = document.createElement('div');
	outer.style.position = "absolute";
	outer.style.top = "0px";
	outer.style.left = "0px";
	outer.style.visibility = "hidden";
	outer.style.width = "200px";
	outer.style.height = "150px";
	outer.style.overflow = "hidden";
	outer.appendChild (inner);

	document.body.appendChild (outer);
	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';
	var w2 = inner.offsetWidth;
	if (w1 == w2) w2 = outer.clientWidth;

	document.body.removeChild (outer);

	return (w1 - w2);
}

function geom_resize_table(divid, tableid, tbodyid) {
	// Get elements:
	var div   = document.getElementById(divid);
	var table = document.getElementById(tableid);
	var tb    = document.getElementById(tbodyid);

	// compute wi
	tbw = tb.offsetWidth;
	sbw = geom_sb_width(); 

	div.style.width = (parseInt(tbw) + sbw) + "px"; 

	// Initialize column max widths:
	var widths = new Array();
	var c, r;
	for (c = 0; c < table.rows[0].cells.length; c++) {
		widths[c] = parseInt(table.rows[0].cells[c].offsetWidth);
	}

	// Find column max widths cycling through all row/col:
	for (r = 1; r < table.rows.length; r++) {
		row = table.rows[r];
		for (c = 0; c < row.cells.length; c++) {
			w = parseInt(row.cells[c].offsetWidth);
			if (w > widths[c]) widths[c] = w; // We have a new max!
		}
	}

	// Set cell width to max col width:
	for (r = 0; r < table.rows.length; r++) {
		row = table.rows[r];
		for (c = 0; c < row.cells.length; c++) {
			row.cells[c].style.width = widths[c] + "px";
		}
	}

	// get table row width:
	rw = parseInt(table.rows[0].offsetWidth);

	// Set div width as row width + scroll bar:
	div.style.width = (rw + sbw) + "px"; 

	// Update height to auto:
	// tab.style.height = "auto";
	// div.style.height = "auto";
}

function geom_resize_elem(id, par) {
	ptop = parseInt($("#"+par).position().top);
	heig = parseInt($(window).height());
	var table = document.getElementById(id);
	//table.style.maxHeight = (heig - ptop - 50) + "px";
	table.style.height    = Math.min(table.height, (heig - ptop - 50)) + "px";

}

function geom_height_max(id, par) {
	ptop = parseInt($("#"+par).position().top);
	heig = parseInt($(window).height());
	var el = document.getElementById(id);
	el.style.height = el.style.maxHeight = (heig - ptop - 50) + "px";
}

function geom_vector_redraw(id) {
	// Get vector components (container and content):
	var div = document.getElementById(id);
	var tab = document.getElementById('vtable_'+id);
	
	// Compute max vector height:
	ptop = parseInt($("#"+id).position().top);
	heig = parseInt($(window).height());

	h = (heig - ptop - 50) + "px";

	// Set container Max Height:
	div.style.maxHeight = h;

	// Get Tab width & height:
	w = tab.offsetWidth;
	h = tab.offsetHeight;

	// Get colwitdh ratios:
	var cols = [];
	var ts   = 0;
	for (i = 0; i < tab.tHead.rows[0].cells.length; i++) {
		cols[i] = Math.max(parseFloat(tab.tHead.rows[0].cells[i].offsetWidth), parseFloat(tab.tBodies[0].rows[0].cells[i].offsetWidth));
		ts +=  cols[i];
	}

	tab.style.width = ts;
		 
	// Set new col widths:
	cw = 0;
	for (i = 0; i < tab.tHead.rows[0].cells.length; i++) {
		for (j = 0; j < tab.tBodies.length; j++) {
			cw += cols[i];
			tab.tBodies[j].rows[0].cells[i].style.width = "" + cols[i] + "px";
		}
	}	

	// Set auto overflow on tbody (+ block display on all table components):
	tab.tHead.style.display       = "block";
	tab.tFoot.style.display       = "block";
	tab.tBodies[0].style.display  = "block";
	tab.tBodies[0].style.overflow = "auto";

	//console.log("cw = " + cw + ", body width = " + parseFloat(tab.offsetWidth));
	// compute size of the slider if present:
	delta = (parseFloat(tab.offsetWidth) - parseFloat(tab.tBodies[0].rows[0].offsetWidth)) / (tab.tHead.rows[0].cells.length - 1);
	//tab.tHead.style.width = 
	//console.log("delta = " + delta);

	//console.log("padding = " + tab.tHead.rows[0].cells[0].style.padding);

	for (i = 0; i < tab.tHead.rows[0].cells.length; i++) {
		tab.tHead.rows[0].cells[i].style.width = tab.tFoot.rows[0].cells[i].style.width = tab.tBodies[0].rows[0].cells[i].style.width;
		if (tab.tHead.rows[0].cells[i].offsetWidth != tab.tBodies[0].rows[0].cells[i].offsetWidth) {
			delta = tab.tBodies[0].rows[0].cells[i].offsetWidth - tab.tHead.rows[0].cells[i].offsetWidth;
			tab.tHead.rows[0].cells[i].style.width = "" + (parseInt(tab.tHead.rows[0].cells[i].style.width) + delta) + "px";
			tab.tFoot.rows[0].cells[i].style.width = "" + (parseInt(tab.tFoot.rows[0].cells[i].style.width) + delta) + "px";
		}
	}
	
}

function geom_maximize(id) {
	//var w = parseInt($(window).width());
	//var x = parseInt($("#" + id).position().left); 
	//var  sh = parseInt($(screen).height());
	//var  dh = parseInt($(document).height());

	// get window height:
	var h = parseInt($(window).height());

	// get element top: 
	var y = parseInt($("#" + id).position().top); 
	
	var a = parseInt($("#" + id ).height());
	
	if (a > h - y - 100 ) {
		// recompute the element height as window height - top of elem - 100px: 
		$("#" + id ).height((h - y - 100) + "px");
	}
	
	//alert("before: " +  $("#" + id ).height() ", x = " + x + ", y = " + y + ", height = " + (h - 100) + "px");
}

function geom_fill_vertical(pid, id) {
	$("#" + id).height((parseInt(geom_inner_height()) - parseInt($("#" + pid).position().top)) + "px");
}

function geom_fill_horizontal(pid, id) {
	$("#" + id).width((parseInt(geom_inner_width()) - parseInt($("#" + pid).position().left)) + "px");
}

function geom_fill_both(pid, id) {
	geom_fill_vertical(pid, id);
	geom_fill_horizontal(pid, id);
}

function geom_resize(id, width, height) {
	if (width > geom_inner_width())
		width = (parseInt(geom_inner_width()) - 20) + "px";
	if (height > geom_inner_height())
		height = (parseInt(geom_inner_height()) - 20) + "px";
	$("#"+id).width(width);
	$("#"+id).height(height);
}

function geom_center(id) {
	var div = document.getElementById(id);
	if (div == null) return;
	// compute position of the popup:
	ww = parseInt(geom_inner_width());
	wh = parseInt(geom_inner_height());

	ow = parseInt(div.offsetWidth);
	oh = parseInt(div.offsetHeight); 

	var x = (ww / 2) - (ow / 2);
	var y = (wh / 2) - (oh / 2);

	// set pos:
	div.style.left = x + "px"; 
	div.style.top  = y + "px"; 
}
