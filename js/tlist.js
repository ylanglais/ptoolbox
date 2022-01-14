
function tlist_go(id, table, flist, start, page, where, edit) {
	data = { "table": table, "fieldlist": flist, "start": start, "page": page, "where": where, "edit": edit};
	//console.log("tlist_go("+ id + "," + JSON.stringify(data));
	load(id, "tlist_act.php", { "table": table, "fieldlist": flist, "start": start, "page": page, "where": where, "edit": edit} );
	//$("#" + id).load("tlist_act.php", { "table": table, "fieldlist": flist, "start": start, "page": page, "where": where, "edit": edit} );       
}

function tlist_view(id, params) {
    $("#"+id).empty();
    //console.log("tlist_view(" + id+ ", "+ JSON.stringify(params)+")");
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
}
