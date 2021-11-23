
function glist_go(id, table, flist, start, page, where, edit) {
	//console.log("glist_go(" + id+ "...)");
	load(id, "glist_act.php", { "table": table, "fieldlist": flist, "start": start, "page": page, "where": where, "edit": edit} );
	//$("#" + id).load("glist_act.php", { "table": table, "fieldlist": flist, "start": start, "page": page, "where": where, "edit": edit} );       
}

function glist_view(id, params) {
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
}
