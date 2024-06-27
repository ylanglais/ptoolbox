function progress(id, rpt_key) {
	var r = ctrl("stats", {"stats_key": rpt_key});
	if (r == false) {
		document.getElementById(id).innerHTML = '<div id="a_' + rpt_key + '" class="data_modal"><div class="data_modal_hourglass"><img src="images/wait.gif" width="100px"/><br/>Computing...</div></div>';
	} else {
		document.getElementById(id).innerHTML = '<div id="a_' + rpt_key + '" class="data_modal"><div class="data_modal_hourglass"><progress id="tt" max="100" value="0"></progress><br/>Computing...</div></div>';
		step = r.max * 10.;
		inter = setInterval(function() { 
			e = document.getElementById('tt');
			if (!e) clearInterval(inter);
			else {
				e.value += 1; 
				e.innerHTML = "" + e.value + "%";
				if (e.value == 100) clearInterval(inter);
			}
		}, step);
	}
}
