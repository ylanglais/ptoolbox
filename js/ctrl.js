function ctrl(ctrl, data, id = null, sync = true) {
	data.ctrl = ctrl; 

	if (id == null) { 
		if (sync) return sync_post("ctrl.php", data);
		else      return      post("ctrl.php", data);
	} 
	if (sync == false) {
		sync_load(id, "ctrl.php", data);
	} else {
		load(id, "ctrl.php", data);
	}
	return true;	
}
