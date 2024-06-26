function fsel_apply(popupid) {
	ctl = null;
	data = {};
	e = document.getElementById("fsel_ctrl");
	if (e != null) ctl = e.value;
	e = document.getElementById("fsel_data");
	if (e != null) data = JSON.parse(e.value);
// console.log("data: " + JSON.stringify(data));

	a = [];
	s = document.getElementById("fsel_sel");
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
function fsel_add() {
	a = document.getElementById("fsel_ava");
	s = document.getElementById("fsel_sel");
	fsel_move(a, s);
}
function fsel_rem() {
	a = document.getElementById("fsel_ava");
	s = document.getElementById("fsel_sel");
	fsel_move(s, a);
}
function fsel_up() {
	var sel = document.getElementById("fsel_sel")
	var opts = sel.options;
	for (var i = 1; i < opts.length; i++) {
		if (opts[i].selected) {
			opt = opts[i];
			sel.remove(i);
			sel.add(opt, i - 1);
		}
	}
}

function fsel_dw() {
	var sel  = document.getElementById("fsel_sel")
	var opts = sel.options;
	for (var i = opts.length - 2; i >= 0; i--) {
		if (opts[i].selected) {
			var opt = opts[i];
			sel.remove(i);
			sel.add(opt, i + 1);
		}
	}
}

