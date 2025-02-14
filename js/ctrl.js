function ctrl(ctrl, data, id = null, sync = true, dld = false) {
	data.ctrl = ctrl; 

	if (id == null) { 
		if      (dld)  return download("ctrl.php", data);
		else if (sync) return sync_post("ctrl.php", data);
		else      return      post("ctrl.php", data);
	} 
	if (sync == false) {
		sync_load(id, "ctrl.php", data);
	} else {
		load(id, "ctrl.php", data);
	}
	return true;	
}
