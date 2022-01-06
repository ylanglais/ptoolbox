<?php 
if (!isset($_SESSION)) session_start(); 
#
header('content-type: text/css');
include("colors.php");
require_once("lib/style.php");
$fontfamily = style::value("fontfamily");
echo <<<EOB
/* 
 * Container definitions:
 */
body {
	font-family: $fontfamily;
	font-size: 10pt;
	font-size: 1.2vw;
	text-align: justify;
	color: $fg;
	background-color: $bg;
}

/* Div definitions: */
div {
	background-color: transparent;
}
#body {
	position: fixed; 
	top: 100px; 
	bottom: 0px; 
	right: 0px; 
	left: 315px; 
	overflow: auto;
}
div.scrollable {
	width: 100%;
	height: 100%;
    /* max-height: 300px; */
    overflow: auto;
    box-shadow: -1px -1px 2px $shadow;
}
div.popup {
	background-color: $bg;
    box-shadow: 5px 5px 12px $shadow;
}
div.heading {
	width: 100%; 
	height: 100px; 
	position: fixed; 
	margin: 0px;
	top: 0px; 
	left: 0px; 
	right:0px;
	padding: 0px;
    box-shadow: 3px 3px 5px $shadow;
	background-color: transparent;
}
div.prjs {
	margin: 0px;
}
div.menu {
	position: fixed; 
	top: 	  100px; 
	bottom:     0px; 
	left: 	    0px; 
	width:    300px; 
	overflow:  auto;
    box-shadow: 3px 3px 5px $shadow;
}

div.block {
	page-break-inside: avoid;
	page-break-before: auto;
	page-break-after:  auto;
}
div.chapters {
}
div.chapter {
	page-break-inside: avoid;
}
div.pie {
	text-align: center;
	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
}
div.graph {
	text-align: center;
	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
}
div.form {
    box-shadow: 3px 3px 5px $shadow;
	padding: 10px;
}

/* */
div.navigation img {
	vertical-align: middle;
}

/* Minical style: */
div.minical {
	background: $bg;
	box-shadow: 2px 2px 4px $shadow;
}
div.data_area {
    box-shadow: 3px 3px 5px $shadow;
}
th.minical.month {
	text-align: center;
	vertical-align: middle;
	background-color: $light_bg;
	color: $fg;

}
td.minical.now {
	text-align: center;
	vertical-align: middle;
	border-color: $selected_bg;
	background-color: $selected_bg;
	color: $selected_fg;
}
th.minical.norm {
	text-align: center;
	vertical-align: middle;
	background-color: $light_bg;
	color: $fg;
}
td.minical.norm {
	text-align: center;
	vertical-align: middle;
	border-style: solid;
	border-width: 1px;
	border-color: $border;
	background-color: $light_bg;
	color: $fg;
}
th.minical.pre, td.minical.pre {
	vertical-align: middle;
	text-align: center;
	background-color: $reverse_bg;
	color:  $reverse_fg;
}



/* 
 * Paragraph definitions: 
 */

p {
	font-family: $fontfamily;
	font-size: 1vw;
	margin-top: 0.6vh;
	margin-bottom: 0.4vh;
	color: $fg;
	text-align: justify;

	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
}
p.img {
	text-align: center;
	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
}
p.majstamp {
	font-size: .5vh;
	color: #555555;
}
/* 
 * headings definitions: 
 */
h6, h1 {
	font-family: $fontfamily;
	font-size: 1.6vw;
	margin-top: 2.3vh;
	margin-bottom: .47vh;
	background-color: $reverse_bg;
	font-weight: bold;
	color: $reverse_fg;
	text-align: justify;
	page-break-after: avoid;
	page-break-before: auto;
}
h2 {
	font-family: $fontfamily;
	font-size: 1.2vw;
	font-weight: bold;
	margin-top: 1.3vh;
	margin-bottom: 0.3vh;
	color: $normal;
	text-align: justify;
	page-break-after: avoid;
	page-break-before: auto;
}
h3 {
	color: $normal;
	font-family: $fontfamily;
	font-style: normal;
	font-size: 1vw;
	font-style: italic;
	margin-top: 0.3cm;
	margin-bottom: 0.1cm;
	border: none;
	padding: 0cm;
	color: $normal;
	text-align: justify;
	page-break-after: avoid;
	page-break-before: auto;
}
h4 {
	font-family: $fontfamily;
	font-size: 10pt;
	font-weight: normal ;
	margin-top: 0.3cm;
	margin-bottom: 0.1cm;
	color: $normal;
	text-align: justify;;
	page-break-after: avoid;
	page-break-before: auto;
}
/* 
 * headings definitions (for index): 
 */
h1.tmat {
	margin: 0px;
	margin-top: 0.1cm;
	margin-bottom: 0cm;
	font-size: 11pt;
	font-weight: normal;
	background-color: $bg;
	color: 		$normal;
}
h2.tmat {
	font-weight: normal;
	font-style: normal;
	font-weight: normal;
	margin: 0px;
	margin-left: 1cm;
	margin-top: 0.1cm;
	font-size: 10pt;
	border: none;
	background-color: $bg;
	color: 		$normal;
}
h3.tmat {
	margin: 0px;
	margin-left: 2cm;
	font-size: 9pt;
	font-weight: normal;
	text-decoration: none;
	background-color: $bg;
	color: 		$normal;
}
h4.tmat {
	font-size: 9pt;
	font-weight: normal;
	margin: 0px;
	margin-left: 2.5cm;
	margin-bottom: 0cm;
	background-color: $bg;
	color: 		$normal;
	margin-bottom: 0cm;
	font-size: 11pt;
	font-weight: normal;
	background-color: $bg;
	color: 		$normal;
}
h2.tmat {
	font-weight: normal;
	font-style: normal;
	font-weight: normal;
	margin: 0px;
	margin-left: 1cm;
	margin-top: 0.1cm;
	font-size: 10pt;
	border: none;
	background-color: $bg;
	color: 		$normal;
}
h3.tmat {
	margin: 0px;
	margin-left: 2cm;
	font-size: 9pt;
	font-weight: normal;
	text-decoration: none;
	background-color: $bg;
	color: 		$normal;
}
h4.tmat {
	font-size: 9pt;
	font-weight: normal;
	margin: 0px;
	margin-left: 2.5cm;
	margin-bottom: 0cm;
	background-color: $bg;
	color: 		$normal;
}

/*
 * Emphasise:
 */
em {
	color: 		$normal;
	font-weight: normal;
	font-style: italic;
}

/*
 * Menu definitions:
 */
ul.menu {
	margin: 0px;
	border: 0px;
	padding: 0px;
}
li.menu {
	background: $normal;
	list-style-type: none;
	font-weight: bold;
	font-size: 14px;
	color: white;
	padding-top: 5px;
	padding-bottom: 5px;
	cursor: pointer;
}
li.menusub {
	background: gray;
	list-style-type: none;
	font-weight: bold;
	font-size: 14px;
	color: white;
	padding-top: 5px;
	padding-bottom: 5px;
	cursor: pointer;
}
ul.menusub {
	display: none;
	background: white;
	color: $normal;
	font-weight: normal;
	font-size: 11pt;
	overflow: auto;
    box-shadow: inset 1px 1px 2px $shadow;
}
ul.menusubsub {
	display: none;
	background: $light_bg;
	color: $normal;
	font-weight: normal;
	overflow: auto;
	font-size: 10pt;
    box-shadow: -1px -1px 2px $shadow;
}
li.menuentry{
	list-style-type: none;
	background: none;
	color: $normal;
}
span.rptlink {
	font-size: 9pt;
	color: $normal;
}
a.logout {
	color: $normal;
	font-size: 14pt;
	cursor: pointer;
}
a.menu {
	color: white;
}
a.logout:hover,a.menu:hover {
	color: $hover;
}
a.rptlink,
a.menuentry {
	text-decoration: none;
	color: 		$normal;
	font-weight: normal;
	cursor: pointer;
}
a.rptlink:link,
a.menuentry:link    { color: $normal; }
a.rptlink:visited,
a.menuentry:visited { color: $normal; }
a.rptlink:hover,
a.menuentry:hover   { 
	color: $hover; 
}

a.menuentry.current {
	color: $selected_fg;
	font-weight: normal;
}
/* 
 * Anchor definitions:
 */ 
a:link    { color: blue }
a:visited { color: #800080 }

a.prjlink {
	cursor: pointer;
	text-decoration: none;
	color: 		$normal;
}
a.prjlink:hover {
	color: $hover;
}

a.prjlink.current {
	color: $normal;
}

a.tmat, a.tmat:visited {
	text-decoration: none;
	color: 		$normal;
}
a.tmat:hover {
	color: 		$hover;
}
/*
 * Image definitions: 
 */
img {
	/*max-width: 80% ;*/
/*	width: 80%; */
	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
}
img.logo, img.heading {
	max-height: 100px;
	height: 75px;
}
/* 
 * Table definitions:
 */
table {
	font-size: 10pt;
	margin: 0px auto;
	border-collapse: collapse;
	padding: 5px;
	border: 0px;
}
table.spacing {
	width: 100%;
	height: 100%;
	border: 0px;
	text-align: center;
	vertical-align: middle;
	table-layout: fixed;
	margin: auto;
}
table.report {
	width: 80%;
	text-align: center;
	vertical-align: middle;
	table-layout: fixed;
	margin: auto;
	word-wrap: break-word;
	overflow-x:auto;
}
tr.spacing {
    width: 100%;
    text-align: center;
    vertical-align: middle;
	border: 0px;
}
td.spacing {
    text-align: center;
    vertical-align: middle;
    padding-right: 0.25cm;
    padding-left: 0.25cm;
	border: 0px;
}
table.glist {
	border: 0.5px solid $normal;
}
table.heading, tr.heading{
	width: 100%;
	height: 100%;
	border: 0px;
	margin: 0px;
	padding: 0px;

}
td.heading {
	border: 0px;
	margin: 0px;
	padding: 2px;
	height:  100px;
	width: 300px;
	color:   $normal;
	text-align: center;
}
td.header {
	margin: 0px;
	padding: 2px;
	color:   $normal;
	text-align: center;
	font-weight: bold;
}

td.head{
	margin: 0px;
	padding: 2px;
	color:   $normal;
	text-align: center;
}
tr {
	border: 0px solid $normal;
	padding: 3px;
}
tr.over {
	color: $hover;
	background-color: $normal; 
	box-shadow: 1px 1px 3px $shadow;
}
tr.selected {
	color: $reverse_fg;
	background-color: $normal;
	font-weight: bold;
}
table.subform tr.selected {
	box-shadow: 1px 1px 2px $shadow;
} 
tr.totaux {
	color: $normal;
	font-weight: bold;
	font-style: italic;
	padding: 3px;
	font-style: italic;
}
th {
	background-color: $reverse_bg;
	color: $reverse_fg;
	padding: 3px;
	text-align: center;
}
td {
	border: 0px solid $normal;
	padding: 5px;
	vertical-align: middle;
}
table.glist tr {
	border: 0px solid $normal;
}
table.glist tr td {
	border: 0px solid $normal;
}
td.submit {
	text-align: right;
}
td.hdr {
	color: $normal;
	text-align: center;
	font-weight: bold;
}
td.hdr.over {
	color: $reverse_fg;
	text-align: center;
	font-weight: bold;
}
td.red {
	color: red;
}
td.right {
	text-align: right;
}
td.number {
	text-align: right;
}
td.num {
	text-align: right;  
	color: $normal; 
	font-style: italic;
}
tr.over td.num {
	text-align: right;  
	color: $hover;
	font-style: italic;
}
td.url { 
	text-align: center; 
}
td.bad { 
	text-align: center; 
	color: red;    
	font-weight: bold;
}
td.dread {
	text-align: center; 
	background-color: red; 
	color: yellow;    
}
td.bof {
	text-align: center; 
	color: orange; 
}
td.tdover {
	color: $hover;
	background-color: $normal;
	cursor: pointer;
}
td.total {
	color: $normal;
	text-align: center;
	font-style: italic;
	border-bottom: 2px solid $normal;
	font-weight: bold;
}
td.total.number {
	color: $normal;
	text-align: right;
	font-style: italic;
	border-bottom: 2px solid $normal;
	font-weight: bold;
}
td.total.number.red {
	color: red;
	text-align: right;
	font-style: italic;
	border-bottom: 2px solid $normal;
	font-weight: bold;
}

/* stat data specifics: */
table.cols2 {
	width: 100%;
	border: 0px;
}
table.cols2 tr {
	width: 100%;
	border: 0px;
}
table.cols2 td {
	width: 50%;
	text-align: center;
	border: 0px;
}
table.stats {
	width: 100%;
	border: 0.5px solid $normal;
	padding: 5px;
}
table.stats tr {
	border: 0.5px solid $normal;
}
table.stats tr.totaux {
	border: 0.5px solid $normal;
}
table.stats td {
	width: 33%;
	border: 0.5px solid $normal;
}
table.stats td.number {
	text-align: right;
}
table.header {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 0px;
}
table.header td {
	border:    1px solid $border;
}
table.footer {
	margin-top: 10px;
	margin-bottom: 10px;
	border: none;
}
table.form {
	border: 0.1px solid $normal;
	border: 0px;
	box-shadow: 2px 2px 4px $shadow;
	margin: 5px;
}
table.subform {
	border: 0.1px solid $normal;
	border: 0px;
	box-shadow: 2px 2px 4px $shadow;
}
table.form tr {
	border: 0px;
}
table.form th {
	border: 0px;
}
table.form td {
	border: 0px;
}
table.subform {
	box-shadow: -2px -2px 4px $shadow;
}
label {
	margin: 4px;
}
input {
	color: $normal;
	background-color: $bg;
	border: 0px;
	box-shadow: -1px -1px 2px $normal;
	margin: 2px;
	margin-left: 4px;
	margin-right: 4px;
	font-family: $fontfamily;
	font-size: 10pt;
}
input.required {
	box-shadow: 0px 0px 3px $hover;
}
input.conflict {
	box-shadow: 0px 0px 2px $conflict;
}
input.set {
	box-shadow: 0px 0px 2px $shadow;
}
button,
input[type="button"],
input[type="submit"] 
{
	box-shadow: 2px 2px 5px $shadow;
	color: $reverse_fg;
	background-color: $normal;
	border: 0px;
	padding: 7px;
	outline-width: 0px;
	outline: none;
}
button:hover,
input[type="submit"]:hover, 
input[type="button"]:hover
{
	color: $reverse_fg;
	box-shadow: -1px -1px 2px $shadow;
	cursor: pointer;
	outline-width: 0px;
	outline: none;
}
button:active,
input[type="submit"]:active, 
input[type="button"]:active 
{
	color: $selected_fg;
	box-shadow: -1px -1px 2px $shadow;
	cursor: pointer;
	/** outline-style: hidden; **/
	/** outline: none; **/
	outline-width: 0px;
}
button:focus,
input[type="submit"]:focus, 
input[type="button"]:focus 
{
	outline-style: hidden;
	outline-width: 0px;
	outline: none;
}
input[type="checkbox"] {
	color: $bg;
	border: 0px;	
}
input[type="checkbox"]:checked {
	color: $reverse_bg;
	background-color: $normal;
}
input[type="checkbox"]:not(:checked) {
	background-color: $bg;
}

.even {
	background-color: $bg;
}
.odd {
	background-color: $light_bg;
}
.over {
	color: $normal;
	background-color: $bg;
	box-shadow: 1px 1px 2px $shadow;
	cursor: pointer;
}
/* rpt elements: */

div.pie {
	margin: auto;
	text-align: center;
	page-break-before: avoid;
	page-break-inside: avoid;
	page-break-after: auto;
	margin: auto;
	min-height: 200px;
}
div.section {
	background: white;
	margin: 1.2vw;
	padding: .5vw;
	/* box-shadow: 3px 3px 5px rgba(10,10,10,.5); */
}
div.section.bg {
	background: #f5f5f5;
}
div.subsection {
	background: white;
	margin: .5vw;
	/* box-shadow: inset 1px 1px 2px rgba(127,127,127,.5); */
}
div.subsection.bg {
	background: #f5f5f5;
}
div.center {
	text-align: center;
}
.bg1 {
	background-color: #338ab2;
}
.bg2 {
	background-color: #2a82ac;
}
.bg3 {
	background-color: #055f8d;
}
.bg4 {
	background-color: #8166b2;
}
.bg5 {
	background-color: #673ab7;
}
.bleusource {
	color:#ffffff;
	background-color: #2196F3;
	width: 80%;
	height: 14vh;
	margin: auto;
	
}
.white {
	background-color: #ffffff;
}
.red {
	color:#ffffff;
	background-color: #c32116;
}
.green {
	background-color: #4caf50;
	color: #ffffff;
}
.orange {
	color: #ffffff;
	background-color: #ff9800;
}
.blue {
	color: #ffffff;
	background-color: #2196F3;
}
.tvert {
	color: #4caf50;
	font-size: 1.2vw;
	font-weight: bold;

}
table.entete {
	width: 100%;
	color: #616161;
	vertical-align: middle;
	font-size: 1.2vw;
/*	font-weight: bold; */
	table-layout: fixed;
}
table.bg_results {
	margin: auto;
	color: #ffffff;
	width:  11vw;
	height: 11vw;
	text-align: center;
	vertical-align: middle;
}
table.pie {
	margin: auto;
	background-color: #ffffff;
	text-align: center;
	vertical-align: middle;
}
table.lcpblock_array {
	background-color: #ffffff;
	text-align: center;
	vertical-align: middle;
	/* width: 100%; */
	 table-layout: fixed;
	/*height: 7.26cm;*/
	width: 100%;
	height: 100%;
}
table.mobiles tr {
}
table.mobiles td {
}
table.w47 {
	min-width: 7cm;
	height: 2.5cm;
	text-align: center;
	vertical-align: middle;
	margin: auto;
	table-layout: fixed;
}
table.w47 td, table.w47 th {
	width: 50%;
}
.ligne1 {
	font-size: 1.2vw;
	font-weight: bold; 
	text-align: center;
	vertical-align: middle;
}
.rbb {
	border-right: 0.5mm solid #616161;
}
.lbb {
	border-left: 0.5mm solid #616161;
}
.bluebold {
	font-weight: bold; 
	color: #2a82ac;
}
.lbw {
	border-left: 0.7mm solid white;
}
.ligne2 {
	font-size: 2.3vw;
	font-weight: bold; 
	text-align: center;
	vertical-align: middle;
}
.ligne3 {
	font-size: 2.3vw;
	text-align: center;
	vertical-align: middle;
}
.ligne4 {
	font-size: 1vw;
	font-style: italic;
	text-align: center;
	vertical-align: middle;
}
.h2 {
	font-size: 1.2vw;
	font-weight: bold;
	color: #616161;
	text-align: justify;
}
table.spacing {
	width: 100%;
	height: 100%;
	text-align: center;
	vertical-align: middle;
	table-layout: fixed;
	margin: auto;
}
tr.spacing {
	width: 100%;
	text-align: center;
	vertical-align: middle;
	border: 0px;
}
td.spacing, th.spacing {
	width: 100%;
	text-align: center;
	vertical-align: middle;
	padding-right: 1vw;
	padding-left: 1vw;
	border: 0px;
}
table.analyse_results {
	width: 100%;
	text-align: center;
	vertical-align: middle;
}
table.analyse_results tr {
	width: 100%;
	text-align: center;
	vertical-align: middle;
	border: 0px;
}
table.analyse_results td {
	text-align: center;
	vertical-align: middle;
	border-width: 0px;
	border-color: black;
}
table.results {
	/* force table to be centered within a td */
	margin: 0 auto;
	margin-top: 2.3vh;
	margin-bottom: 2.3vh;
	width: 75%;
	border: 0px;
/* table-layout: fixed; */
}
table.results tr {
	text-align: right;
	padding: .5vw;
	margin:  .9vw;
	border: 0px;
}
table.glist   tr:nth-child(even),
table.results tr:nth-child(even),
table.results tr:nth-child(even) td,
table.results tr:nth-child(even) td.hdr {
	background-color: $bg;
}
table.glist   tr:nth-child(odd),
table.results tr:nth-child(odd),
table.results tr:nth-child(odd) td,
table.results tr:nth-child(odd) td.hdr { 
	background-color: $light_bg;
} 
table.glist tr.nav,
table.glist tr.but {
	background-color: $bg;
}
table.results th {
	text-align: right;
	background-color: $reverse_bg;
	color: $reverse_fg;
	padding: .5vw;
	margin: .9vw;
	border: 0px;
}

img.logo {
	max-width:  220px;
	max-height: 70px;
}


/* svg elements: */
line {
	stroke:$fg; stroke-width: 1px;
}
line.graph.border {
	stroke:#444444; stroke-width: 1px;
}
line.graph.border.top {}
line.graph.border.bottom {}
line.graph.border.left {}
line.graph.border.right {}
graph.line.tick{ 
	stroke:$fg; stroke-width: 1.5px;
}
graph.line.stick{
	stroke:#444444; stroke-width: 1px;
}
graph.line.tick.xtick {}
graph.line.tick.ytick {}
graph.stick.xstick {}
graph.stick.ystick {}
line.grid {
	stroke:#777777; stroke-width: .5px;
}
line.grid.xgrid {}
line.grid.ygrid {}

line.graph.axis {
	stroke:#444444; stroke-width: 1px;
}
line.graph.axis.xaxis { }
line.graph.axis.yaxis { }

text.graph.empty {
	font-size: 1.2vw;
	text-anchor: middle;
	alignment-baseline: middle;
}
text.graph.title {
	font-size:1vw;
	font-weight: bold;
	text-anchor:middle;
	alignment-baseline:middle;
	fill: #616161;
}
text.graph.label {
	font-size:.9vw;
}
.graph.legend { font-size: .9vw }
text.graph.units.xunits,
text.graph.units.yunits,
text.graph.label.xlabel,
text.graph.label.ylabel,
text.graph.units { 
	font-size: .7vw;
	font-weight: normal;
	fill: #616161;
}
text.graph.legend {
	font-size: .9vw;
	font-weight: normal;
}
text.graph.pie.legend{
	font-size: clamp(6px, .7vw, 16px);
	fill: black;
}
line.graph.legend {
	stroke-width: 2px;
}
/* Special elements: */
#cover {
	page-break-after: always;
}
#table_matiere {
	page-break-after: always;
}

/* Counter definitions: */
body             {counter-reset: h1;}
div.chapters     {counter-reset: h1;}
h1.rpt,h1.tmat   {counter-reset: h2;}
h2.rpt,h2.tmat   {counter-reset: h3;}
h3.rpt,h3.tmat   {counter-reset: h4;}

h1.rpt:before    {counter-increment: h1; content: counter(h1) " "}
h2.rpt:before    {counter-increment: h2; content: counter(h1) "." counter(h2) " "}
h3.rpt:before    {counter-increment: h3; content: counter(h1) "." counter(h2) "." counter(h3) " "}
h4.rpt:before    {counter-increment: h4; content: counter(h1) "." counter(h2) "." counter(h3) "." counter(h4) " "}

h1.tmat:before   {counter-increment: h1; content: counter(h1) " "}
h2.tmat:before   {counter-increment: h2; content: counter(h1) "." counter(h2) " "}
h3.tmat:before   {counter-increment: h3; content: counter(h1) "." counter(h2) "." counter(h3) " "}
h4.tmat:before   {counter-increment: h4; content: counter(h1) "." counter(h2) "." counter(h3) "." counter(h4) " "}
EOB
?>
