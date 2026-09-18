function image_show(e, id) {
    var d = el(id);
	if (d.style.display == 'none')	{
		d.style.display = 'block';
		var x = e.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		var y = e.clientY - d.offsetHeight + (document.documentElement.scrollTop ?  document.documentElement.scrollTop : document.body.scrollTop);
		if (y < 0) y = 0;
		d.style.top  = y + 'px';
		d.style.left = x + 'px';
		//alert( "image position = (" + d.style.left + ", " + d.style.left + ")");
	}
} 
function image_hide(id) {
    el(id).style.display = 'none';
} 
function image_clear(id) {
	el(id).value                = "";
	el("path_"      + id).value = "";
	el("name_"      + id).value = "";
	el("whole_"     + id).src   = "images/no_image.png";
	el("thumbnail_" + id).src   = "images/tn_no_image.png";
}

function image_hover(img, hsrc) {
	img.nsrc = img.src;
	img.src = img.hsrc;
}

function image_norm(img) {
	img.src = img.nsrc;
}
