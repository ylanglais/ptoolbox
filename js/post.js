function _load(id, url, params, completef) {
	var xhr = new XMLHttpRequest();

	xhr.id = id;
	xhr.completef = completef;
	
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-Type", "application/json");
	xhr.onreadystatechange = function() { 
		if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
			console.log("id = " + this.id );
			e = document.getElementById(this.id);
			console.log(">>> "+ JSON.stringify(xhr.responseText));
			if (e != null) {
				e.innerHTML = xhr.responseText;
				console.log("loaded to id");
			} else {
				console.log("error: no element with id " + this.id );
			}
		}
	}
	xhr.send(JSON.stringify(params));
}
function load(id, url, params) {
	fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: JSON.stringify(params) } )
	.then(response => response.text())
	.then(html     => { document.getElementById(id).innerHTML = html; })
	.catch(function(err) { console.log(err); });
} 
function post(url, data) {
	fetch(url, { method: "POST", credentials: 'same-origin', cache: 'no-cache', headers: {  'Content-Type': 'application/json' }, body: data} )
	.then(response => response.text())
	.then(html     => { window.open(response.text());})
	.catch(function(err) { console.log(err); });
}
function sync_post(url, data) {
    var ws = null;
	ws = new XMLHttpRequest();
    url = url + "?rnd=" + Math.random(); 
    
    ws.open("POST", url, false); //false means synchronous
    ws.setRequestHeader("Content-Type", "application/json; charset=utf-8");
    ws.send(JSON.stringify(data));
	if (ws.response) return JSON.parse(ws.response);
	return false;
}
>>>>>>> 2f3afc8402a84f617b2a7ad1d998d4a6b4ffa982
