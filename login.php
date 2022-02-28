<html>
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>
<style> 
body { margin: 10px }
input[type=text], input[type=password] {
	border: 0px;
	box-shadow: -1px -1px 2px #444444;
	padding: 7px;
	margin: 10px;
}
input[type=button],
input[type=submit] {
	width: 50px;
}

</style>
</head>
<body style="background-color: #F8F8F6; font-family: arial, Helvetica, sans;" onresize="geom_center('login')" onload="geom_center('login')">
<script type="text/javascript" src="js/geom.js"></script>
<form action="index.php" method="post" autocomplete="off">

<div id="login" style="position: absolute; background-color: white; width: 15%; min-width: 200px; text-align: center; padding: 15px; border-radius: 5px; box-shadow: 10px 10px 5px #555555;">
<table align="center">
<tr><td><label for="login">Login</label></td><td><input type="text" name="login"/></td></tr>
<tr><td><label for="passwd">Password</label></td><td><input type="password" id="passwd" name="passwd"/></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Ok"/></td></tr>
</table>
<div style='width: 100%; left: 0px; bottom: 0px; right: 0px; text-align: center; font-size: smaller;'><a href='passwd_reset.php'>Reset your password</a></div>
</div>
</form>
<script>
geom_center("login");
</script>
</body>
</html>
