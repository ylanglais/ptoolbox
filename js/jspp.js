function jspp_hide(id) {
console.log(document.getElementById("_"+id).src);
	document.getElementById(id).style.visibility = "collapse";
	document.getElementById("_"+id).src = "images/sarrow.down.white.png";
console.log(document.getElementById("_"+id).src);
	//document.getElementById(id).style.display = "none";
}
function jspp_show(id) {
	document.getElementById(id).style.visibility = "visible";
	document.getElementById("_"+id).src = "images/sarrow.right.white.png";
	//document.getElementById(id).style.display = "block";
}
function jspp_toggle(id) {
	e = document.getElementById(id);
	if  (e.style.visibility == "visible" || e.style.visibility == '') jspp_hide(id);
	else jspp_show(id);
}
function jspp_hide_all(id) {
}
function jspp_show_all(id) {
}
