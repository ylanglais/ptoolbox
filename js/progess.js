function genid() {
	return "id"+ Math.floor(Math.random() * 100000);
}
function progress(id, rpt_key) {
	var r = ctrl("stats", {"stats_key": rpt_key});
	div = document.getElementById(id);
	if (div == null) {
		console.log("no element called '" + id + "'");
		return;
	}
	if (r === false || r == null) {
		div.innerHTML = '<div id="a_' + rpt_key + '" class="data_modal"><div class="data_modal_hourglass"><img src="images/wait.gif" width="100px"/><br/>Computing...</div></div>';
	} else {
		if (r.max < 2) return;
		pid = genid();
		div.innerHTML = '<div id="a_' + rpt_key + '" class="data_modal"><div class="data_modal_hourglass"><progress id="'+pid+'" max="100" value="0"></progress><br/>Computing...</div></div>';
		step =  r.max / 100.;
		inter = setInterval(() => { 
			e = document.getElementById(pid);
			if (!e) {
				clearInterval(inter);
			} else {
				e.value += 1; 
				e.innerHTML = "" + e.value + "%";
				if (e.value == 100) {
					clearInterval(inter);
				}
			}
		}, step);
		//console.log("progess lauched with: " + step);
	}
}
