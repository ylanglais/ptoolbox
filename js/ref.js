function ref_get(ref, f1, f2, id1, id2) {
dbgp();
	let e1 = el(id1);
	let e2 = el(id2);
	if (e1 === null) dbg("e1 is null...");
	if (e2 === null) dbg("e2 is null...");

	if (e2.value != "") return;
	if (e1.alt !== undefined && e1.alt != "") {
		e2.value = e1.alt;
		return;
	}
	r = ctrl("ref", {"action": "ref_get", "ref": ref, "skey": f1, "dkey": f2, "val": e1.value});
	if (typeof r == "object") {
		if (!Array.isArray(r)) {
			e2.value = r[f2];
		}
	}
}

