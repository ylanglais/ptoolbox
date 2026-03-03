<?php

chdir("..");
require_once("lib/session.php");
require_once("lib/prov.php");



global $_session_;
#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
#
# Check 
$_session_->check();
#

$p = new prov("db", "default.ref.country");

$l = $p->fdata("name");
$pdata = $p->data();

print("<br/><input id='inp' type='text' oninput='ac_change(this, \"$pdata\", \"name\")' onmouseout='event.stopPropagation();' onchange='console.log(this.value)'/><br/>");

print("<p> toto.titi </p><p> zerazerazerazerazerazerazeraz </p>");
#print("<select size='5'> <option value='1'>toto1</option> <option value='2'>toto2</option> <option value='3'>toto3</option> <option value='4'>toto4</option> <option value='5'>toto5</option> <option value='6'>toto6</option> <option value='7'>toto7</option> <option value='8'>toto8</option></select>"); 

/* with datalist:
print("<input id='inp' type='text' list='dltest' autocomplete='on' oninput='ac_change(this, $pdata, \"name\")'/><datalist id='dltest'>");
foreach ($l as $v) {
	print("<option value='$v'>$v</option>");
}
print("</datalist>");
*/
?>
