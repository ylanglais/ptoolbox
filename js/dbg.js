function dbg(mixed) {
	if (typeof mixed === 'object' | typeof mixed === 'array') {
		mixed = JSON.stringify(mixed);
	}
	console.log(mixed);
}
function tojs(mixed) {
	return JSON.stringify(mixed);	
}
