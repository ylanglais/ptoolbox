<html>
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>
</head>
<body style="background-color: #F8F8F6; font-family: helvetica, sans;" onresize="geom_center('login')" onload="geom_center('login')">
<script type="text/javascript" src="js/geom.js"></script>
<form action="index.php" method="post" autocomplete="off">

<div id="login" style="position: absolute; background-color: white; text-align: center; padding: 30px; box-shadow: 10px 10px 5px #555555;">
<table align="center">
<tr><td><label for="login">Login</label></td><td><input type="text" name="login"/></td></tr>
<tr><td><label for="passwd">Password</label></td><td><input type="password" id="passwd" name="passwd"/></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Ok"/></td></tr>
</table>
<div style='width: 100%; left: 0px; bottom: 0px; right: 0px; text-align: center;'><a href='passwd_reset.php'>Reset your password</a></div>
</div>
</form>
<script>
geom_center("login");
</script>
</body>
</html>