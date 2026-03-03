<script src='../js/draw.js'></script>
<div>
<div id="draw" onmousemove="draw_show_xy(this, event)"  onclick='draw_line_deselect(event)' style="display: block; position: absolute; top: 100px; left:100px; height: 700px; width: 700px; background-color: lightblue;">
	<svg onload='draw_init()' height="700px" width="700px" xmlns="http://www.w3.org/2000/svg">
	<line id='l1' x1="10" y1="10" x2="260" y2="260" style="stroke:black;stroke-width:2px" onclick="draw_line_select(this, event);" />
</svg> 
</div>
<div id='panel'>
<label for='x'>x</label><input id='x' type='text' value=''/>
<label for='y'>y</label><input id='y' type='text' value=''/>
<label for='y'>button</label><input id='buttoamou' type='text' value=''/>
<label for='object'>Object</label><input id='object' type='text' value=''/>
<label for='x1'>x1</label><input id='x1' type='text' value=''/>
<label for='y1'>y1</label><input id='y1' type='text' value=''/>
<label for='x2'>x2</label><input id='x2' type='text' value=''/>
<label for='y2'>y2</label><input id='y2' type='text' value=''/>
</div></div>
<?php
?>
