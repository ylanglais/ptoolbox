/* 
 * https://stackoverflow.com/questions/667555/detecting-idle-time-in-javascript-elegantly 
 */

var timeout_value;
var timeout_id;
function timeout_set(minutes) {
	console.log("Start timer");
    window.onload = timeout_reset;

	timeout_value = minutes;

    /*  
	 * DOM Events 
     */

    document.onmousemove  = timeout_reset;
    document.onkeypress   = timeout_reset;
	document.onload       = timeout_reset;

	/*
	 * document.onmousedown  = timeout_reset; // touchscreen presses
	 * document.ontouchstart = timeout_reset;
	 * document.onclick      = timeout_reset; // touchpad clicks
	 * document.onscroll     = timeout_reset; // scrolling with arrow keys
	 * document.onkeypress   = timeout_reset;
	 */
}
function timeout_logout(what) {
	if (what == null) {
		console.log("timeout")
	} else {
		console.log(what);
	}
	$("#menusubmit").submit();
}
function timeout_reset() {
	sec = 1000;
	min = 60;
	clearTimeout(timeout_id);
	timeout_id = setTimeout(timeout_logout, timeout_value * min * sec);
}

/*
 * Dproerly disconnect on close:
 */ 
window.onclose        = timeout_logout("window onclose");
window.onbeforeunload = timeout_logout("window onbeforeunload");
