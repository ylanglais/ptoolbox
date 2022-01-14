
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

function post(path, params) {
    method = "post";
    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

	//	console.log("method: " + form.method + ", action: " + form.action)
	console.log("method: " + method + ", action: " + path)

    for(var key in params) {
		console.log("key: " + key + ", val: " + params[key]);
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);
	form.submit();
}


