<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script hold the login page for   */
/* editors                               */
/* ===================================== */
?>

<html>
<head>
<title>Netkaart Editor Login</title>
</head>
<body>
  <h2>User Login </h2>
  <form name="login" method="post" action="login.php?ID=<?php echo $_GET['ID']; ?>">
  <table>
	 <tr><td> Username: </td><td> <input type="text" name="username"> </td></tr>
	 <tr><td> Password: </td><td> <input type="password" name="password"> </td></tr>
  	 <tr><td><input type="submit" name="submit" value="Login!"></td></tr>
  </table>
  </form>
</body>
</html>