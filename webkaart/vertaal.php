<?php 
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script sets the contents of the  */
/* feedbackform in the webkaart,         */
/* Uses a sidebar at the right side      */
/* outputs inside html so no header etc  */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}
if (isset($_GET['tekst'])) {
	echo vertaal($_GET['tekst']);
} else {
	echo '';
}
?>
