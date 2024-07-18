
async function load(id, url, params) {
	const response = await fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(params) });
	const text     = await response.text();
	var e = document.getElementById(id);
	if (e == null) { console.log("element " + id + " doesn't exit"); return; }
	
	e.innerHTML = text;
}
function async_load(id, url, params, fct, fct_parm) {
	fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(params) } )
	.then(response => response.text())
	.then(html     => { if (id != null) document.getElementById(id).innerHTML = html; if (typeof fct == "function") fct(fct_param); })
	.catch(function(err) { console.log(err); });
} 
function post(url, data) {
	fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: data} )
	.then(response => response.text())
	.then(html     => { open(response.text());})
	.catch(function(err) { console.log(err); });
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

