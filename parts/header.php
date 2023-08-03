<?php
header('Content-type: text/html; charset="utf-8"');
header('Cache-Control: max-age=1, must-revalidate');
require_once("lib/style.php");
$title = style::value("application_title");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<title><?php echo $title ?></title>
<meta name="robots"      content="none" />
<meta name="generator"   content="gvim" />
<meta name="author"      content="Yann LANGLAIS" />
<meta charset=utf-8 />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">

<link rel='shortcut icon' type='image/x-icon' href='images/favicon.ico' />
<link rel="stylesheet"   type="text/css" href="style.php"/>
<script type="text/javascript" src="js/jquery/jquery.js"></script>
<?php
foreach (['js', 'usr/js'] as $dir) {
	if ($h = opendir($dir)) {
		$dirs = array();
		while (false !== ($js = readdir($h)))
			if (substr($js, -3) == ".js") array_push($dirs, $js);
		#sort($dirs);

		foreach ($dirs as $js) print("<script type=\"text/javascript\" src=\"$dir/$js\"></script>\n");
	}
}
?>

</head>
<body onbeforeunload='timeout_logout("body onbeforeunload")'> 