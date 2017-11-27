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
if (isset($_GET['ID'])) {$itemid=$_GET['ID'];} else {$itemid='<onbekend>';}

$form =  '<div class="feedbackform"><h1>'.vertaal('Feedback formulier').'</h1><br>';
$form .=  vertaal('Dank dat u wil helpen met het verbeteren van onze netkaart, vul alstublieft het formulier in').'.<br>'. vertaal('Mochten wij naar aanleiding van uw bericht nog vragen hebben, dan nemen we contact met u op').'.<p>';
$form .= '<form name="feedback" action="">';
$form .= '<p>'.vertaal('Uw naam').':<br /><input name="feedbackname" /></p>';
$form .= '<p>'.vertaal('Uw email').':<br /><input name="feedbackemail" /></p>';
$form .= '<p>'.vertaal('Onderwerp').':<br /><input name="feedbacksubject" value="'.vertaal('Feedback netkaart').' '.$itemid.'"/></p>';
$form .= '<p class="antiverveeltjes">Laat dit gvd leeg<br /><input name="url" /></p>';
$form .= '<p>'.vertaal('Uw feedback').':<br /><textarea id="feedbacktextarea" onkeydown="if(event.keyCode == 13) {document.getElementById(\'feedbacktextarea\').value = document.getElementById(\'feedbacktextarea\').value + \'\n\'; return false;};" name="feedbackmessage" rows="10"></textarea></p>';
$form .= '<p>'.vertaal('Controle getal').':<br /><input name="feedbackgetal" size=5 style="width:200px;"><img style="float:right;" src="camimp.php?id='.rand(0,1000000).'"></p>';
$form .= '<a id="balloon-feedbacksent" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;" title="'.vertaal('Verzend feedback').'">'.vertaal('Verzenden').'</a></form>';
$form .= '</div><div class="balloon-logo"><a target="_blank" href="https://www.hoogspanningsnet.com" title="'.vertaal('Naar de website').'"><img src="'.$IconDir.'embleem_hoogspanningsnet_transpa.png">';
$form .= '</div>';

echo $form; 
?>
