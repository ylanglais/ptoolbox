let pop = null;

function popup_new(id, title, url, param, formid) { 
	if (document.getElementById(id) != null) return; 

	p = document.createElement("div");

	p.id               = "popup_" + id;
	p.className        = "popup";
	p.style.position   = "absolute";
	p.style.visibility = "hidden";



	s  = "<table class='popup_bar'><tr><td class='title' onmousedown='popup_mouse_down(event, \"popup_" + id + "\")'" 
	   + " onmouseup='popup_mouse_up()'>" + title + "</td><td class='close'> <img width='20px' src='images/close.norm.png' onmouseover='this.src=\"images/close.pre.png\"' onmouseout='this.src=\"images/close.norm.png\"' onclick='this.src=\"images/close.sel.png\";popup_destroy(\"" + id + "\")'></td></tr> </table>"
	   + "<div id='popup_"+id+"_data' class='popup_data'></div>";


    p.innerHTML = s;
    document.body.appendChild(p);

	load("popup_"+id+"_data", url, param);
 	geom_center("popup_" + id);

    p.style.display    = "block";
    p.style.overflow   = "hidden";
    p.style.visibility = "visible";
	

    if (typeof(formid) == "string") {
		let form = document.getElementById(formid)
		if (form != null) form.addListenerListener("submit", popup_destroy(id), false);
	}
}

function popup_ctrl(id, title, ctl, data, formid) { 
	if (document.getElementById(id) != null) return; 

	p = document.createElement("div");

	p.id               = "popup_" + id;
	p.className        = "popup";
	p.style.position   = "absolute";
	p.style.visibility = "hidden";



	pid = "popup_"+id+"_data";

	s  = "<table class='popup_bar'><tr><td class='title' onmousedown='popup_mouse_down(event, \"popup_" + id + "\")'" 
	   + " onmouseup='popup_mouse_up()'>" + title + "</td><td class='close'> <img width='20px' src='images/close.norm.png' onmouseover='this.src=\"images/close.pre.png\"' onmouseout='this.src=\"images/close.norm.png\"' onclick='this.src=\"images/close.sel.png\";popup_destroy(\"" + id + "\")'></td></tr> </table>"
	   + "<div id='"+pid+"' class='popup_data'></div>";

    p.innerHTML = s;
    document.body.appendChild(p);

	ctrl(ctl, data, "popup_"+id+"_data");


 	geom_center("popup_" + id);


    p.style.display    = "block";
    p.style.overflow   = "hidden";
    p.style.visibility = "visible";
	

    if (typeof(formid) == "string") {
		let form = document.getElementById(formid)
		if (form != null) form.addListenerListener("submit", popup_destroy(id), false);
	}
}


function popup_mouse_get(event) {
    if (event.offsetX || event.offsetY)
        return {"x": parseInt(event.pageX), "y": parseInt(event.pageY)};
	return null;
}

function popup_mouse_down(event, id) {
	event.stopPropagation();
    p = document.getElementById(id);
    if (!p) return;
	
    pos = popup_mouse_get(event);
    p.dx = pos.x - p.offsetLeft;
    p.dy = pos.y - p.offsetTop;
	pop = p;
	//pop.mouseoutlstnr = pop.addEventListener("mouseout", popup_mouse_up,{capture: true});
	pop.mousemovlstnr = document.addEventListener("mousemove", popup_mouse, {capture: true});
}

function popup_mouse_up(id) {
	if (!pop) return;
    //let p = document.getElementById(id);
    pop.dx = pop.dy = 0;
	//pop.removeEventListener("mouseout",  pop.mouseoutlsnr);
	document.removeEventListener("mousemove", pop.mousemovlstnr);
	pop = null;
	event.stopPropagation();
}

function popup_mouse(event) {
	function _min(a, b)  { if (a < b) return a; return b; }
	function _max(a, b)  { if (a > b) return a; return b; }
    if (pop == null) return;
    pos = popup_mouse_get(event);
	event.stopPropagation();

	x = parseInt(pop.style.left);
	y = parseInt(pop.style.top);

	maxx = window.innerWidth  - pop.clientWidth;
	maxy = window.innerHeight - pop.clientHeight;

	pop.style.left = _max(0, _min(pos.x - pop.dx, maxx)) + "px";
	pop.style.top  = _max(0, _min(pos.y - pop.dy, maxy)) + "px";
}

function popup_show(id) {
    var p = document.getElementById("popup_" + id);
    if (p == null) return;

    geom_center("popup_" + id);

// show:
    pop.style.visibility = 'visible';
    pop.style.display = 'block';
}

function popup_hide(id) {
    var p = document.getElementById("popup_" + id);
    if (p == null) return;
    p.style.visibility = 'hidden';
}

function popup_visibility(id) {
    var p = document.getElementById("popup_" + id);
    if (p == null) return;
    return p.style.visibility;
}

function popup_destroy(id) {
	p = document.getElementById("popup_"+id)
	if (p) p.remove();
	pop = null;
}
