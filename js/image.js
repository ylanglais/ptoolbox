function image_show(e, id) {
    var d = document.getElementById(id);
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
    document.getElementById(id).style.display = 'none';
} 
function image_clear(id) {
	document.getElementById(id).value                = "";
	document.getElementById("path_"      + id).value = "";
	document.getElementById("name_"      + id).value = "";
	document.getElementById("whole_"     + id).src   = "images/no_image.png";
	document.getElementById("thumbnail_" + id).src   = "images/tn_no_image.png";
}

function image_hover(img, hsrc) {
	img.nsrc = img.src;
	img.src = img.hsrc;
}

function image_norm(img) {
	img.src = img.nsrc;
}
