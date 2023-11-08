function ctrl(ctrl, data, id = null,  sync = true ) {
	data.ctrl = ctrl; 

	if (id == null) return sync_post("ctrl.php", data);
	
	if (sync == false) {
		async_load(id, "ctrl.php", data);
	} else {
		load(id, "ctrl.php", data);
	}
	
}
