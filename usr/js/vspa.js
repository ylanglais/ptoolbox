function vspa_search() {
	if ((e   = el("vspa_search")) === null) return;
	if ((res = el("vspa_result")) !== null) res.innerHTML = ''; 
	if ((det = el("vspa_detail")) !== null) det.innerHTML = ''; 

	dbg("phrase: " + e.value);
	ctrl("vspa", { "qry": e.value }, "vspa_result");
}
function vspa_detail(id) {
	dbgp();
	ctrl("vspa", { "partid": id }, "vspa_detail");
}
