<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script hold the login page for   */
/* editors                               */
/* ===================================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}

echo '<div class="edititem-outline"><div class="edititem-header"><h1>'.vertaal('Gebruiker login').'</h1></div>';
echo 	'<form name="login" id="webkaart-loginform" method="post" action="">';
echo 	'<table>';
echo 		'<tr><td> '.vertaal('Gebruikersnaam').' : </td><td> <input type="text" name="username"> </td></tr>';
echo 		'<tr><td> '.vertaal('Wachtwoord').': </td><td> <input type="password" name="password"> </td></tr>';
echo 		'<tr><td colspan=2><div class="edititem-header"><a id="edititem-login" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;" title="Login">'.vertaal('Inloggen').'</a></div></td></tr>';
echo 	'</table>';
echo '</form>';
echo '</div>';

?>
  		  		