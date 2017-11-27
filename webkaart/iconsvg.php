<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the station-    */
/* icons with the right color based on   */
/* stijlen.kml                           */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');

if (isset($_GET['spans'])) { $span = explode('-',$_GET['spans']);};
$nrsp = sizeof($span);
$type = 'none';
$naam = '';
$scaleIcon = 1.0;
if (isset($_GET['type'])) { $type = $_GET['type'];};
if (isset($_GET['naam'])) { $naam = $_GET['naam'];};
if (isset($_GET['scale'])) { $scaleIcon = 1.2;};

$stijlen = simplexml_load_file($CommonFileDir.'stijlen.xml');
$scale = 1.0;
$font_size = 12;

function getHVIconBlockColor($spann) {
	global $stijlen;
	$kl = 'white';
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND intval($spann) <= floatval($kleurstijl['HoogGrens'])) {
		$kl = $kleurstijl->StationsIcoon->Icoon['Color'];
	}
	endforeach;
	return '#'.$kl;
}

function getHVIconTextColor($spann) {
	global $stijlen;
	$kl = 'white';
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND intval($spann) <= floatval($kleurstijl['HoogGrens'])) {
		$kl = $kleurstijl->StationsIcoon->Label['Color'];
	}
	endforeach;
	return '#'.$kl;
}

function getIconScale($type, $spann){
	global $stijlen;
	$sc = 1.0;
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		return floatval($kleurstijl->$type->Icoon['Size']);
	}
	endforeach;
	return $sc;
}

$svgstring[] = '<?xml version="1.0" standalone="no"?>';
$svgstring[] = '<svg height="100%" width="100%" version="1.1" xmlns="http://www.w3.org/2000/svg">';

if ($type=='none') {
	$svgstring[] =	'<g font-family="Arial" font-weight="bold" font-size="18" fill="white" text-anchor="end">';

	for ($i = 0; $i <= $nrsp-1; $i++) {
		$svgstring[] =	'<rect x="0"   y="'.($i*19).'" width="33" height="17" fill="'.getHVIconBlockColor($span[$i]).'" />';
		$svgstring[] =	'<text x="32" y="'.(($i*19)+15).'" fill="black">'.$span[$i].'</text>';
		$svgstring[] =	'<text x="31" y="'.(($i*19)+14).'">'.$span[$i].'</text>';
	}
	if ($nrsp>1) {$txtoffset = (($nrsp-1)*19)+7;} else {$txtoffset=26;};
	$svgstring[] =	'<text x="36" y="'.$txtoffset.'" text-anchor="start" fill="'.getHVIconTextColor($span[0]).'" style="stroke-width:0.5; stroke:rgb(0,0,0);">'.$naam.'</text>';
	$svgstring[] =	'</g>';
}

if ($type=='mast') {
}

if ($type=='knpp') {
}

$svgstring[] = '</svg>';
header('Content-Type: image/svg+xml');
echo(join("\n", $svgstring));
?>
