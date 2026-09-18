function json_encode(data) {
	try {
		return JSON.stringify(data);
	} catch (error) {
		err(error);
	}
	return false;
}
function json_decode(data) {
	try {
		return JSON.parse(data);
	} catch (error) {
		err(error);
	}
	return false;
}
	
function dbg(mixed, mixed2 = null) {
	calr = dbg.caller;
	if (calr === null || typeof calr !== 'object' || calr.hasOwnProperty(name) === false) 
		clog("DBG",  mixed, mixed2);
	else
		clog("DBG from "+ calr.name, mixed, mixed2);
}

function anames(fstr) {
	const STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
	const ARGUMENT_NAMES = /([^\s,]+)/g;

	fstr  = fstr.replace(STRIP_COMMENTS, '');
	var result = fstr.slice(fstr.indexOf('(')+1, fstr.indexOf(')')).match(ARGUMENT_NAMES);
	if (result === null) result = [];
	return result;
}
function dbgp() {
	callr = dbgp.caller;
	cargs = callr.arguments;
	anams = anames(callr.toString());
	
	str   = callr.name + " called with (" ;
	for (i = 0; i < anams.length; i++) {
		if (i > 0) str += ', ';
		str += anams[i] + ": " 
		if (typeof cargs[i] === 'object') { 
			str += json_encode(cargs[i]); 
		} else str += cargs[i];
	} 
	dbg(str + ')');
}
function err(mixed, mixed2 = null) {
	calr = err.caller;
	clog("ERR from " + calr.name, mixed, mixed2);
}
function clog(type, mixed, mixed2 = null) {
	let str = "";
	let label = null;
	if (mixed2 !== null) {
		label = mixed;
		mixed = mixed2;
	}	
	if (type !== null) str += type + ": ";
	if (label !== null) str += label + " ";

	if (typeof mixed === 'object' | typeof mixed === 'array')
		str += json_encode(mixed);
	else  
		str += mixed;

	console.log(str)
}
function tojs(mixed) {
	return json_encode(mixed);	
}
function tstamp() {
	//date = new Date();
	 //return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) +  dif + pad(Math.floor(Math.abs(tzo) / 60)) + ':' + pad(Math.abs(tzo) % 60);
}
