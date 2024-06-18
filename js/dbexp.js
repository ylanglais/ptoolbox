dbexp_cur_dbs   = null;
dbexp_cur_table = null;

function dbexp_clean(id) {
	let e = document.getElementById(id);
	if (e != null) e.innerHTML = "";
}
function dbexp_tables(e, dbs) {
	if (dbexp_cur_dbs != null) {
		dbexp_cur_dbs.classList.remove("current");
	}
	dbexp_cur_dbs = e;
	dbexp_cur_dbs.classList.add("current");
	dbexp_clean("dbexp_data");
	ctrl("dbexp", {"action": "dbexp_table_list", "dbs": dbs}, "dbexp_tables");
}

function dbexp_data(e, dbs, table) {
	if (dbexp_cur_table != null) {
		dbexp_cur_table.classList.remove("current");
	}
	dbexp_cur_table = e;
	dbexp_cur_table.classList.add("current");
	ctrl("dbexp", {"action": "dbexp_data", "dbs": dbs, "table": table}, "dbexp_data");
}

