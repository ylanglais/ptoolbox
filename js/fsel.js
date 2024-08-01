function fsel_apply(popupid, fid) {
	ctl = null;
	data = {};
	e = document.getElementById("fsel_ctrl_" + fid);
	if (e != null) ctl = e.value;
	e = document.getElementById("fsel_data_" + fid);
	if (e != null) data = JSON.parse(e.value);

	a = [];
	s = document.getElementById("fsel_sel_" + fid);
	for (o of s.options) {
		a.push(o.value);
	}
	data.fsel =	a;
	popup_destroy(popupid);	
	ctrl(ctl, data);
	menu_data_reload();
}
function fsel_cancel(popupid) {
	popup_destroy(popupid);	
}
function fsel_move(f, t) {
	if (f == null || t == null) return;
	for (i = 0; i < f.length; i++) {
		if (f.options[i].selected) {
			var opt;
			opt = document.createElement('option');
			opt.text  = f.options[i].text;
			opt.value = f.options[i].value;
			try {
				t.add(opt, null);
			} catch (ex) {
				t.add(opt);
			}
			f.remove(i); i--;
		}
	}
}
function fsel_add(fid) {
	a = document.getElementById("fsel_ava_" + fid);
	s = document.getElementById("fsel_sel_" + fid);
	fsel_move(a, s);
}
function fsel_rem(fid) {
	a = document.getElementById("fsel_ava_" + fid);
	s = document.getElementById("fsel_sel_" + fid);
	fsel_move(s, a);
}
function fsel_up(fid) {
	var sel = document.getElementById("fsel_sel_" + fid)
	var opts = sel.options;
	for (var i = 1; i < opts.length; i++) {
		if (opts[i].selected) {
			opt = opts[i];
			sel.remove(i);
			sel.add(opt, i - 1);
		}
	}
}

function fsel_dw(fid) {
	var sel  = document.getElementById("fsel_sel_" + fid)
	var opts = sel.options;
	for (var i = opts.length - 2; i >= 0; i--) {
		if (opts[i].selected) {
			var opt = opts[i];
			sel.remove(i);
			sel.add(opt, i + 1);
		}
	}
}

