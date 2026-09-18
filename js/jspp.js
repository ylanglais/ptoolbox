function jspp_hide(id) {
dbg(el("_"+id).src);
	el(id).style.visibility = "collapse";
	el("_"+id).src = "images/sarrow.down.white.png";
dbg(el("_"+id).src);
	//el(id).style.display = "none";
}
function jspp_show(id) {
	el(id).style.visibility = "visible";
	el("_"+id).src = "images/sarrow.right.white.png";
	//el(id).style.display = "block";
}
function jspp_toggle(id) {
	e = el(id);
	if  (e.style.visibility == "visible" || e.style.visibility == '') jspp_hide(id);
	else jspp_show(id);
}
function jspp_hide_all(id) {
}
function jspp_show_all(id) {
}
