<?php

require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");
require_once("lib/style.php");
require_once("parts/layout.php");

# Get POST data:
$a = new args();

global $_session_;

if (!isset($_session_)) $_session_ = new session();

if (!$_session_->isnew() && $a->has("page") && $a->val("page") == "logout") {
	$_session_->destroy();
	$a->all_clean();
	header("Location: index.php");
}

include("parts/header.php");
?>
<body>
<div id="body">
	<div id='header' class='heading'>
		<table class='heading'>
			<tr><td align="center"><img src='images/logo.svg' height='60px'/></td><th>VSPA - recherche de pièces automobile</th><td width='50%'></td>
			<td align='left'><div align='center'>Mon panier<br/><img height="50px" src="images/basket.png"/></div></td>
			<td><div align='center'>Mon compte<br/><img height="50px" src='images/account.png'/></div></td></tr>
		</table>
	</div>
<?php 
	print(layout_part("menu", "menu", "menu"));
?>
	<div id='data_area'>
	</div>
</div>
</body>
</html>
