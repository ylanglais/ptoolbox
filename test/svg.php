<div style="backgourd-color: green;">
<svg xmlns:svg="http://www.w3.org/2000/svg"
	xmlns="http://www.w3.org/2000/svg"
	version="1.1"
	width="400"
    height="200">
    
    <style>
		.draggable { cursor: move; }
    </style>
    
    <script type="text/ecmascript"><![CDATA[
    var selectedElement = 0;
    var currentX = 0;
    var currentY = 0;
    var currentMatrix = 0;
	var color = "";

	function selectElement(evt) {
		selectedElement = evt.target;
    	currentX = evt.clientX;
    	currentY = evt.clientY;
    	currentMatrix = selectedElement.getAttributeNS(null, "transform").slice(7,-1).split(' ');
		color = selectedElement.getAttribute("stroke");
		selectedElement.setAttribute("stroke", "red");

		for (var i = 0; i < currentMatrix.length; i++) {
			currentMatrix[i] = parseFloat(currentMatrix[i]);
		}
      
		selectedElement.setAttributeNS(null, "onmousemove", "moveElement(evt)");
		selectedElement.setAttributeNS(null, "onmouseout", "deselectElement(evt)");
		selectedElement.setAttributeNS(null, "onmouseup", "deselectElement(evt)");
	}
        
	function moveElement(evt) {
  		var dx = evt.clientX - currentX;
      	var dy = evt.clientY - currentY;
      	currentMatrix[4] += dx;
      	currentMatrix[5] += dy;
      
      	selectedElement.setAttributeNS(null, "transform", "matrix(" + currentMatrix.join(' ') + ")");
      	currentX = evt.clientX;
      	currentY = evt.clientY;
    }
        
    function deselectElement(evt) {
		if(selectedElement != 0){
			selectedElement.removeAttributeNS(null, "onmousemove");
			selectedElement.removeAttributeNS(null, "onmouseout");
		    selectedElement.removeAttributeNS(null, "onmouseup");
            selectedElement = 0;
		    selectedElement.setAttribute("stroke", color);
		}
	}
    ]]> </script>
    

    <rect x="0.5" y="0.5" width="399" height="199" fill="none" stroke="black"/>
    
    <rect class="draggable"
          x="30" y="30"
          width="80" height="80"
          fill="blue"
          transform="matrix(1 0 0 1 0 0)"
          onmousedown="selectElement(evt)"/>
          
    <rect class="draggable"
          x="160" y="50"
          width="50" height="50"
          fill="green"
          transform="matrix(1 0 0 1 0 0)"
          onmousedown="selectElement(evt)"/>

</svg>
</div>
<div style="background-color: grey;" width="100%" height="600px">
<p>test</p>
<svg mlns:svg="http://www.w3.org/2000/svg"
     xmlns="http://www.w3.org/2000/svg"
     version="1.1"
     width="600"
     height="500" style="background-color: lightgrey;">
	<line id="line" x1="10" y1="100" x2="400" y2="300"  stroke="rgb(255,0,0)" stroke-width="5" onclick="line_click('line')"/>
	<g id="arc" stroke='orange' stroke-width='5' fill='none' onclick="line_click('arc')" >
	<path id='arrow-$id' style='' d='M 10 100 A 600 600 0 0 1 400 300'/>
	</g >
</svg>
</div>
<script>
var sel;
var color;
function line_click(id) {
	sel = document.getElementById(id);
	//alert(id + " stroke color: " + sel.getAttribute("stroke") + ", stroke-width: " + sel.getAttribute("stroke-width"));
	color = sel.getAttribute("stroke");
   	sel.setAttribute("stroke", "blue");     
	sel.setAttributeNS(null, "onmouseup", "sel.setAttribute('stroke', color)");
}
</script>
