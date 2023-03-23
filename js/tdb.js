function tdb_get_data(what) {
	$("#tdb_data").load(what /* + "_data.php" */, {'fromdate': $("#fromdate").val(), 'todate': $("#todate").val()});
}
function tdb_hdr(module, titre) {
	$("#data_area").load("tdb.php", {'module': module, 'titre': titre});
}
function tdb_form(module, titre) {
	$("#data_area").load("tdb_form.php", {'module': module, 'titre': titre});
}
function tdb_page(page) {
	$("#data_area").load(page);
}
function tdb_table(page, datalink) {
	$("#data_area").load("gui.php", {'page': page, 'datalink': datalink});
}

