function ctrl(ctrl, data, id = null,  sync = true) {
	data.ctrl = ctrl; 

	if (id == null) {
		sync_post("ctrl.php", data);
	} else if (sync == false) {
		async_load(id, "ctrl.php", data);
	} else {
		load(id, "ctrl.php", data);
	}
	
}
