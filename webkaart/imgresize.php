<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script the contents of the       */
/* balloons in the webkaart, 3rd version */
/* Uses a sidebar at the right side      */
/* outputs inside html so no header etc  */
/* ===================================== */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');
require('hulpfuncties.php');

function isValidImageURL ($url)
// copied from http://stackoverflow.com/questions/676949/best-way-to-determine-if-a-url-is-an-image-in-php
{
	$params = array('http' => array(
			'method' => 'HEAD'
	));
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp)
		return false;  // Problem with url
		
		$meta = stream_get_meta_data($fp);
		if ($meta === false)
		{
			fclose($fp);
			return false;  // Problem reading data from url
		}
		
		$wrapper_data = $meta["wrapper_data"];
		if(is_array($wrapper_data)){
			foreach(array_keys($wrapper_data) as $hh){
				if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19
				{
					fclose($fp);
					return true;
				}
			}
		}
		
		fclose($fp);
		return false;
}

function printerror ($tekst){
	echo '<div class="header">';
	echo '<h1>'.vertaal("FOUT: Geen data gevonden").'</h1>';
	echo '</div>';
	echo '<div class="foto-portrait"><img class="foto" src="files/fout_geenfoto_blank.png">';
	echo '</div>';
	echo '<div class="gegevens">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tekst;
	echo '</div>';
}

if (isset($_GET['url'])) 	{ 	$imgURL = $_GET['url']; }
if (isset($_GET['size'])) 	{	$imgSZ = $_GET['size']; }

$FotoURLOK = isValidImageURL($imgURL);
if ($FotoURLOK) {
	
}


?>