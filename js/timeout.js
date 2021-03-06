/* 
 * https://stackoverflow.com/questions/667555/detecting-idle-time-in-javascript-elegantly 
 */

var timeout_value;
function timeout_set(minutes) {
	console.log("Start timer");
    window.onload = timeout_reset;

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
	clearTimeout(timeout_value);
	timeout_value = setTimeout(timeout_logout, 10 * min * sec);
}

/*
 * Dproerly disconnect on close:
 */ 
window.onclose        = timeout_logout("window onclose");
window.onbeforeunload = timeout_logout("window onbeforeunload");
