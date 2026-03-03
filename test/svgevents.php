<script src="../jscripts/jquery-2.0.3.min.js"></script>
<script src="../jscripts/widget.js"></script>
<!-- script src="../jscripts/pcDataType.js"></script-->
<style>
#dtest {
	z-index: 4;
	position: fixed;
	background-image: url(../images/pacman.svg); 
/* 	top: 400px; left: 200px; */
	width: 400px; height: 400px;
	vertical-align:  center;
	text-align: middle;
/*	border: solid black 1px; */
}
#red-sphere {
	position: fixed;
	z-index: 5;
	/*background-image: url(../images/red-sphere.svg);*/
 	top: 200px; left: 240px; 
	width: 200px; height: 200px;
	text-align: middle;
	vertical-align:  center;
/*	border: solid black 1px; */
}

</style>
<script>
var last = "";
function onclick_cb(e) { 
	last = $("#evtrpt").val(""); 
	$("#evtrpt").val( "click on red sphere"); 
}
function onmouseover_cb(e) { 
	$("#evtrpt").val("over red sphere"); 
}
function onmouseout_cb(e) { 
	$("#evtrpt").val(""); 
}
</script>

<input id="evtrpt" type="text" value="" /><br/>

<div id="dtest">
<div id="red-sphere">
<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   width="200"
   height="200"
   id="svg2"
   version="1.1"
   inkscape:version="0.47 r22583"
   sodipodi:docname="red-sphere.svg">
  <defs
     id="defs4">
    <linearGradient
       id="linearGradient5078">
      <stop
         id="stop5086"
         offset="0"
         style="stop-color:#ff0000;stop-opacity:0;" />
      <stop
         style="stop-color:#ff0000;stop-opacity:0.98958331;"
         offset="1"
         id="stop5082" />
    </linearGradient>
    <linearGradient
       id="linearGradient5040">
      <stop
         style="stop-color:#ff0000;stop-opacity:1;"
         offset="0"
         id="stop5042" />
      <stop
         id="stop5060"
         offset="0.32051283"
         style="stop-color:#ff0000;stop-opacity:0.74901961;" />
      <stop
         id="stop5056"
         offset="0.64102566"
         style="stop-color:#ff0000;stop-opacity:0.49803922;" />
      <stop
         style="stop-color:#ff0000;stop-opacity:0.70196078;"
         offset="0.82051283"
         id="stop5058" />
      <stop
         style="stop-color:#ff0000;stop-opacity:0.90625;"
         offset="1"
         id="stop5044" />
    </linearGradient>
    <linearGradient
       id="linearGradient3590">
      <stop
         style="stop-color:#ff0000;stop-opacity:0.11458334;"
         offset="0"
         id="stop3592" />
      <stop
         id="stop5054"
         offset="1"
         style="stop-color:#ff0000;stop-opacity:0.23958333;" />
      <stop
         style="stop-color:#ff0000;stop-opacity:1;"
         offset="1"
         id="stop3594" />
    </linearGradient>
    <inkscape:perspective
       sodipodi:type="inkscape:persp3d"
       inkscape:vp_x="0 : 526.18109 : 1"
       inkscape:vp_y="0 : 1000 : 0"
       inkscape:vp_z="744.09448 : 526.18109 : 1"
       inkscape:persp3d-origin="372.04724 : 350.78739 : 1"
       id="perspective10" />
    <radialGradient
       inkscape:collect="always"
       xlink:href="#linearGradient5078"
       id="radialGradient5084"
       cx="379.28574"
       cy="406.78793"
       fx="379.28574"
       fy="406.78793"
       r="106.92857"
       gradientTransform="matrix(1.2006981,1.5835363e-7,-1.6647663e-7,1.2622898,-76.121875,-103.97929)"
       gradientUnits="userSpaceOnUse" />
  </defs>
  <sodipodi:namedview
     id="base"
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1.0"
     inkscape:pageopacity="0.0"
     inkscape:pageshadow="2"
     inkscape:zoom="0.7"
     inkscape:cx="4.7112944"
     inkscape:cy="541.44948"
     inkscape:document-units="px"
     inkscape:current-layer="layer1"
     showgrid="false"
     inkscape:window-width="1280"
     inkscape:window-height="1002"
     inkscape:window-x="1024"
     inkscape:window-y="0"
     inkscape:window-maximized="1" />
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <g
    inkscape:label="Calque 1"
     inkscape:groupmode="layer"
     id="layer1"
     transform="translate(-7.1590728e-7,-852.36217)">
    <path
       sodipodi:type="arc"
       style="fill:url(#radialGradient5084);fill-opacity:1;fill-rule:evenodd;stroke:none"
       id="red-sphere"
       sodipodi:cx="340.71429"
       sodipodi:cy="438.07648"
       sodipodi:rx="106.42857"
       sodipodi:ry="97.14286"
       d="m 447.14287,438.07648 c 0,53.65052 -47.6497,97.14286 -106.42858,97.14286 -58.77887,0 -106.42857,-43.49234 -106.42857,-97.14286 0,-53.65052 47.6497,-97.14286 106.42857,-97.14286 58.77888,0 106.42858,43.49234 106.42858,97.14286 z"
       transform="matrix(0.9395973,0,0,1.0294117,-220.13423,501.40112)"
       onclick="onclick_cb();"
       onmouseover="onmouseover_cb();"
       onmouseout="onmouseout_cb();"
       inkscape:label="red-sphere" />
  </g>
</svg>
</div>
</div>
