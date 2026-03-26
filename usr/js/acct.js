
function acct_login() {
	if ((e = document.getElementById("email")) === null) return; 
	if (e.value == "") {
		document.getElementById("message").innerHTML = "<p><b>le mail doit etre saisi</b></p>";
		return;
	}
	login = e.value;
	if ((e = document.getElementById("passwd")) === null) return; 
	if (e.value == "") {
		document.getElementById("message").innerHTML = "<p><b>le mot de passe doit etre saisi</b></p>";
		return;
	}
	passwd = e.value;
	ctrl('acct', {'login': login, 'passwd': passwd}, 'data_area');
	dbg("reload menu");
	ctrl('menu', {'reload': true}, 'menu');
	dbg("menu reloaded");
}
function acct_professionnel() {
}
function acct_particulier() {
}
