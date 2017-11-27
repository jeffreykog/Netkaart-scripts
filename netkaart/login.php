<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script check if a user is        */
/* logged in and has sufficient rights   */
/* ===================================== */

require('phpsqlajax_dbinfo.php');

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__),'Not connected to MySQL')); }

if (isset($_POST['username']) && isset($_POST['password'])) {
	
	$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_POST['username'].'" and WachWoord="'.md5($_POST['password']).'"';
	$result=mysqli_query($connection, $sql);

	// Mysqli_num_row is counting table row
	$count=mysqli_num_rows($result);

	// If result matched $myusername and $mypassword, table row must be 1 row
	if($count==1){
            /* Cookie expires when browser closes */
        setcookie('editusername', $_POST['username'], false);
        setcookie('editpassword', md5($_POST['password']), false);
        setcookie('editrole', 'EDITOR', false);
        header('Location: edititem.php?ID='.$_GET['ID']);
    } else {
        echo 'Username/Password Invalid';
        echo '<p>Click <a href="loginedit.php?ID='.$_GET['ID'].'">here</a> to try again.';
    }
} else {
    echo 'You must supply a username and password.';
    echo '<p>Click <a href="loginedit.php?ID='.$_GET['ID'].'">here</a> to try again.';
}
//close connection
mysqli_close($connection);

?>