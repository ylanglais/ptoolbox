function tdb_get_data(what) {
	$("#tdb_data").load(what /* + "_data.php" */, {'fromdate': $("#fromdate").val(), 'todate': $("#todate").val()});
}
function tdb_hdr(module, titre) {
	$("#data_area").load("tdb.php", {'module': module, 'titre': titre});
}
function tdb_form(module, titre) {
	$("#data_area").load("tdb_form.php", {'module': module, 'titre': titre});
}
function tdb_rpt(rptname) {
	document.getElementById("data_area").innerHTML = '<div id="a_' + rptname + '" style=" position:absolute;top: 0px; left:0px;width:100%;height:100%;opacity:0.3;z-index:100;background:#000;"><div style="position:absolute; top: 45%;width:100%;text-align: center; vertical-align: middle;color: white;"><img src="images/wait.gif" width="100px"/><br/>Computing...</div></div>';
	$("#data_area").load("tdb_rpt.php", {'rptname': rptname});
}
function tdb_page(page) {
	$("#data_area").load(page);
}
function tdb_table(page, datalink) {
	$("#data_area").load("gui.php", {'page': page, 'datalink': datalink});
}

