<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script check if a user is        */
/* logged in and has sufficient rights   */
/* ===================================== */

require('phpsqlajax_dbinfo.php');

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}

//print_r($_POST[]);

if (isset($_POST['username']) && isset($_POST['password'])) {
	$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_POST['username'].'" and WachWoord="'.md5($_POST['password']).'"';

	// Opens a connection to a MySQL server.
	$connection = mysqli_connect ($server, $username, $password, $database);
	if (!$connection) { die('MySQL Not connected');}
	//Execute query
	$result=mysqli_query($connection, $sql);
	//Close connection to MySQL server
	mysqli_close($connection);
	
	// Mysql_num_row is counting table row
	$count=mysqli_num_rows($result);
	
	// If result matched $myusername and $mypassword, table row must be 1 row
	if($count==1){
            /* Cookie expires when browser closes */
        setcookie('editusername', $_POST['username'], false);
        setcookie('editpassword', md5($_POST['password']), false);
        setcookie('editrole', 'EDITOR', false);
        echo 'logged in';
//        header('Location: edititem.php?ID='.$_GET['ID']);
    } else {
        echo '<i>'.vertaal('Gebruikersnaam/Wachtwoord ongeldig').'.</i><p>';
        }
} else {
    echo '<i>'.vertaal('Er moet een Gebruikersnaam en Wachtwoord worden ingevuld').'.</i><p>';
}
?>