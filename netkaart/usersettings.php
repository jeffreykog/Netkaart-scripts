<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the main KML    */
/* Which contains credits, legend,       */
/* copyright stuff etc.                  */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');
require_once($CommonFileDir.'class.translator.php');

function selectedyes($val, $inp) {
	if ($val == $inp) {
		return("selected ");
	}
};

function vertaal($str){
	global $translate;
	$vert = $translate->__($str);
	return $vert;
}

if (isset($_COOKIE['NKformLanguage'])) {
//	echo 'Cookie NKformLanguage was detected, value='.$_COOKIE['NKformLanguage'];
	$taal = $_COOKIE['NKformLanguage'];
	$translate = new Translator($_COOKIE['NKformLanguage']);
} else {
	$translate = new Translator('nl');
}

if (isset($_POST['formLanguage'])) { 
	$taal = $_POST['formLanguage']; 
	setcookie('NKformLanguage', $_POST['formLanguage'], time()+30*24*3600);
}

if(isset($_POST['submit'])) 
{ 
	if (isset($_POST['formLanguage'])) { 
		$taal = $_POST['formLanguage']; 
		setcookie('NKformLanguage', $_POST['formLanguage'], time()+30*24*3600);
	};
	$SettingsSaved = true;
} else {
	$SettingsSaved = false;
}

echo '<form method="post" action="'.$WebServer.$UseDir.'usersettings.php">';
echo '<fieldset><legend>'.vertaal("Netkaart instellingen").'</legend>';
echo '<input type="hidden" name="submitted" id="submitted" value="1"/>';
echo '<label for="formLanguage">'.vertaal("Taal voorkeur").':</label>';
echo '<select name="formLanguage">';
echo '  <option value="">'.vertaal("Kies").'...</option>';
echo '  <option '.selectedyes($taal,"nl").'value="nl">Nederlands</option>';
echo '  <option '.selectedyes($taal,"de").'value="de">Deutsch</option>';
echo '  <option '.selectedyes($taal,"uk").'value="uk">English</option>';
echo '  <option '.selectedyes($taal,"fr").'value="fr">Francais</option>';
echo '</select>';
echo '<p><input type="submit" name="submit" value="'.vertaal("Instellingen opslaan").'"/>';
if ($SettingsSaved) { echo vertaal("Instellingen opgeslagen"); }
echo '</fieldset>';
echo '</form>';
?>