/***
function hst_show(id) {
	$('#'+id).show();
	el(id).classList.remove('sh');
	el(id).classList.add('hs');
}
function hst_hide(id) {
	$('#'+id).hide();
	el(id).classList.remove('hs');
	el(id).classList.add('sh');
}
function hst_toggle(id) {
	$('#'+id).toggle();
}
 
function __show(id) {
	if ((e = el(id)) === null) return;
	rect = epos(e);
	if (e.hasOwnProperty("intid") && e.intid  !== null) clearInterval(e.intid);
	e.intid = setInterval(fct, 10);
	e.iter  = 0;
	e.fheight = parseInt(rect.height);
	
	function _resize() {
		if (e.iter = 0) {
			e.style.height = "0px";
		} 
		e.iter++;
		h = parseInt(e.style.height);
		if (h < e.fheight) h += 1;	
		else {
			clearInterval(e.intid);
			e.intid = null;
		}
		e.height = h + "px";
	}
}
function __hide(id) {
	if ((e = el(id)) === null) return;
	rect = epos(e);
}
***/
