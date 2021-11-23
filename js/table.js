
var Table = function(jsdata) {
	this.data    = JSON.parse(jsdata);
	this.columns = this.data[0];

	this.npp         = 25;
	this.start       = 0;
	this.sort_c		 = 0;
	this.sort_o		 = 0;	

	this.thead = function() {
		hdr = "<tr><th>#</th>";
		for (k in this.columns) {
			hdr += "<th>" + k + "</th>";
		} 
		hdr += "</tr>";
		return hdr;
	}

	this.draw = function(divid) {
		if (divid === undefined) {
			console.log("Null div id");
			return;
		}
		var div = document.getElementById(divid);
		if (div === undefined) {
			console.log("div '" + divid + "' not found");
			return;
		}
		html = "<table>";
		html += this.thead();
		i = 0;
		for (l in this.data) {
			i++;
			odev = i % 2 ? "even" : "odd";
			html += "<tr class='" + odev + "'><td class='num'>" + i + "</td>";
			for (k in this.columns) {
				html += "<td>" + this.data[i-1][k] + "</td>";
			}	
			html += "</tr>";
		}
		html += this.thead();
		html += "</table>";
		div.innerHTML = html;
	}
}
