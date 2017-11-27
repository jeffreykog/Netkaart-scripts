<?php
require('../phpsqlajax_dbinfo.php');

if (isset($_POST['username']) && isset($_POST['password'])) {
	// Opens a connection to a MySQL server.
	$connection = mysqli_connect ($server, $username, $password, $database);
	if (!$connection) {	die('Not connected'); }
	
	$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_POST['username'].'" and WachWoord="'.md5($_POST['password']).'"';
	$result=mysqli_query($connection, $sql);
	//close the connection
	mysqli_close($connection);
	
	// Mysql_num_row is counting table row
	$count=mysqli_num_rows($result);
	
	// If result matched $myusername and $mypassword, table row must be 1 row
	if($count==1){
            /* Cookie expires when browser closes */
        setcookie('editusername', $_POST['username'], false);
        setcookie('editpassword', md5($_POST['password']), false);
        setcookie('editrole', 'BEHEERDER', false);
        header('Location: beheer.php');
    } else {
        echo 'Username/Password Invalid';
    }
} else {
    echo 'You must supply a username and password.';
}
?>