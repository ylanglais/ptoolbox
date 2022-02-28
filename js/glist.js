
function glist_go(id, pdat, opts, start, lines) {
	console.log(JSON.stringify(opts));
	if (start != null) opts.start = start ;
	if (lines != null) opts.page  = lines ;
	console.log(JSON.stringify(opts));
	
	data = { "prov": pdat, "opts": opts};
	//console.log("glist_go("+ id + "," + JSON.stringify(data));
	load(id, "glist_act.php", data);
	//$("#" + id).load("glist_act.php", data );       
}

function glist_sort(el, pdat, opts, field) {
	f = opts.sort;
	o = opts.order;
	//console.log("new: "+ field, ", old: sortfield: " + f + ", order: " + o);
	
	if (f == field) {
		if (o == 'up') {
			el.classList.remove('up');
			el.classList.add('down');
			o = 'down';
		} else {
			el.classList.remove('down');
			el.classList.add('up');
			o = 'up';
		}
	} else {
		if (f !== false) {
			oe = document.getElementById("sort_" + f);
			oe.classList.remove('sel');
		}	
		el.classList.add('sel');
		o = 'up';
		if (el.classList.contains('down')) 
			o = 'down';
	}
	opts.sort  = field;
	opts.order = o;
	data = { "prov": pdat, "opts": opts};
	load(opts.id, "glist_act.php", data);
}

function glist_view(id, data) {
	load(id, "gform_act.php", data);

/***
    $("#"+id).empty();
    //console.log("glist_view(" + id+ ", "+ JSON.stringify(params)+")");
    //console.log(" id: " + id + " params: " + JSON.stringify(params));
    //$("#" + id).load("ui_act.php", params);
    //load(id, "ui_act.php", params);
    $.ajax({
        url: "ui_act.php",
        type: "POST",
        data: params,
        success: function(response) {
            $("#"+id).append(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log("error : "+xhr.responseText);
        }
    });
***/
}
