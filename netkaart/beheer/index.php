<?php
require('../phpsqlajax_dbinfo.php');

// Check if were logged in, if not goto the start page
if (CheckLoggedIn(TRUE)==true) {header("location:beheer.php");}
?>

<html>
<head>
<title>Netkaart Beheerder Login</title>
</head>
<body>
  <h2>Beheerder Login </h2>
  You are not logged in, or have insufficient rights to access the page.<br>
  <form name="login" method="post" action="login.php">
  <table>
	 <tr><td> Username: </td><td> <input type="text" name="username"> </td></tr>
	 <tr><td> Password: </td><td> <input type="password" name="password"> </td></tr>
  	 <tr><td><input type="submit" name="submit" value="Login!"></td></tr>
  </table>
  </form>
</body>
</html>