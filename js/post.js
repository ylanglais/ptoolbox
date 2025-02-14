async function load(id, url, data) {
	const response = await fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(data) });
	const text     = await response.text();
	var e = document.getElementById(id);
	if (e == null) { console.log("element " + id + " doesn't exit"); return; }
	e.innerHTML = text;
}
function sync_load(id, url, data) {
    var ws = null;
	ws = new XMLHttpRequest();
    url = url + "?rnd=" + Math.random(); 
    ws.open("POST", url, false); //false means synchronous
    ws.setRequestHeader("Content-Type", "application/json; charset=utf-8");
    ws.send(JSON.stringify(data));
	if (ws.response) {
		var e = document.getElementById(id);
		if (e == null) { console.log("element " + id + " doesn't exit"); return; }
		e.innerHTML = ws.response
	}
}
async function post(url, data) {
 	fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(data)} );
}
async function download(url, data) {
	console.log("in download");
 	fetch(url, { 
		method: "POST", 
		credentials: 'same-origin', 
		cache: 'no-cache', 
		headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(data)})
	.then(res  => {
		cdis  = res.headers.get('Content-Disposition'); 
		parts = cdis.split(';');
		fpart = parts[1].split("=");
		fname = fpart[1];
		res.blob().then(blob => { 
			console.log(blob);
			var url = window.URL.createObjectURL(blob); 
			var a      = document.createElement("a");
				a.href = url;
				a.download = fname;
				document.body.appendChild(a);
console.log(document.getElementById("a"));
				a.click(); 
				window.URL.revokeObjectURL(url);
				document.body.removeChild(a);
		});
	 });
}
function sync_post(url, data) {
    var ws = null;
	ws = new XMLHttpRequest();
    url = url + "?rnd=" + Math.random(); 
    
    ws.open("POST", url, false); //false means synchronous
    ws.setRequestHeader("Content-Type", "application/json; charset=utf-8");
    ws.send(JSON.stringify(data));
	if (ws.response) {
		try {
			return JSON.parse(ws.response);
		} catch {
			return null;
		}
	}
	return false;
}

