function hst_show(id) {
	$('#'+id).show();
	document.getElementById(id).classList.remove('sh');
	document.getElementById(id).classList.add('hs');
}
function hst_hide(id) {
	$('#'+id).hide();
	document.getElementById(id).classList.remove('hs');
	document.getElementById(id).classList.add('sh');
}
function hst_toggle(id) {
	$('#'+id).toggle();
}
 
