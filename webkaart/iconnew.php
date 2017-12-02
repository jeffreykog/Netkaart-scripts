<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the station-    */
/* icons with the right color based on   */
/* stijlen.kml                           */
/* ===================================== */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');

// Create image instances
if (isset($_GET['spans'])) { $span = explode('-',$_GET['spans']);};
//Echo $_GET['spans']."<br>";
$nrsp = sizeof($span);
$type = 'none';
$naam = '';
$scaleIcon = false;
if (isset($_GET['type'])) { $type = $_GET['type'];};
if (isset($_GET['naam'])) { $naam = $_GET['naam'];};
if (isset($_GET['scale'])) { $scaleIcon = $_GET['scale'];};

putenv('GDFONTPATH=' . realpath($CommonFileDir));

$stijlen = simplexml_load_file($CommonFileDir.'stijlen.xml');
$scale = 1.0;
$font_size = 12;

function strToHex($string){
	$hex = '';
	for ($i=0; $i<strlen($string); $i++){
		$ord = ord($string[$i]);
		$hexCode = dechex($ord);
		$hex .= substr('0'.$hexCode, -2);
	}
	return strToUpper($hex);
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

function getHVcolor ($spann) {
	global $stijlen;
	global $dest;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);

	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->StationsIcoon->Icoon['Color'], 0, 2);
		$grn = substr($kleurstijl->StationsIcoon->Icoon['Color'], 2, 2);
		$blu = substr($kleurstijl->StationsIcoon->Icoon['Color'], 4, 2);
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function getHVLblcolor ($spann) {
	global $stijlen;
	global $dest;
	global $scale;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);

	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->StationsIcoon->Label['Color'], 0, 2);
		$grn = substr($kleurstijl->StationsIcoon->Label['Color'], 2, 2);
		$blu = substr($kleurstijl->StationsIcoon->Label['Color'], 4, 2);
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function getHVMastcolor ($spann) {
	global $stijlen;
	global $dest;
	global $scale;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);
	//echo $spann;
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->Masticoon->Icoon['Color'], 0, 2);
		$grn = substr($kleurstijl->Masticoon->Icoon['Color'], 2, 2);
		$blu = substr($kleurstijl->Masticoon->Icoon['Color'], 4, 2);
		$scale = floatval($kleurstijl->Masticoon->Icoon['Size'])*0.9;
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function getHVMastLblcolor ($spann) {
	global $stijlen;
	global $dest;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);
	//echo $spann;
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->Masticoon->Label['Color'], 0, 2);
		$grn = substr($kleurstijl->Masticoon->Label['Color'], 2, 2);
		$blu = substr($kleurstijl->Masticoon->Label['Color'], 4, 2);
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function getHVKnppcolor ($spann) {
	global $stijlen;
	global $dest;
	global $scale;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);
	//echo $spann;
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->Neticoon->Icoon['Color'], 0, 2);
		$grn = substr($kleurstijl->Neticoon->Icoon['Color'], 2, 2);
		$blu = substr($kleurstijl->Neticoon->Icoon['Color'], 4, 2);
		$scale = floatval($kleurstijl->Neticoon->Icoon['Size']);
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function getHVKnppLblcolor ($spann) {
	global $stijlen;
	global $dest;

	$kl = imagecolorallocatealpha($dest, 0xFF, 0x00, 0x00, 0);
	//echo $spann;
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
	if (intval($spann) >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
		$red = substr($kleurstijl->Neticoon->Label['Color'], 0, 2);
		$grn = substr($kleurstijl->Neticoon->Label['Color'], 2, 2);
		$blu = substr($kleurstijl->Neticoon->Label['Color'], 4, 2);
		$kl = imagecolorallocatealpha($dest, hexdec($red), hexdec($grn), hexdec($blu), 0);
	}
	endforeach;
	return $kl;
}

function showcolors ($nrsp, $span) {
	global $dest;
	
	$white = imagecolorallocatealpha($dest,255,255,255,0);
	$black = imagecolorallocatealpha($dest,0,0,0,0);
	$font = 'arialnarbd.ttf';
	$fontsize = 13.0;
	for ($i = 0; $i <= $nrsp-1; $i++) {
		$dims=imagettfbbox($fontsize,0,$font,$span[$i]);
		$startX=27-($dims[2]-$dims[0]);
		if (($span[$i]==10) or ($span[$i]==70) or ($span[$i]==100) or ($span[$i]==110) or ($span[$i]==150)) {$startX=$startX-1;}
		imagefilledrectangle($dest, 0, ($i*15)+$i, 28, ($i*15)+$i+14, getHVcolor($span[$i]));
		imagealphablending($dest, true);
		imagettftext($dest, $fontsize, 0, $startX+1, ($i*15)+$i+14, $black, $font, $span[$i]);
		imagettftext($dest, $fontsize, 0, $startX, ($i*15)+$i+13, $white, $font, $span[$i]);
	}
}

function showmastcolors ($span) {
	global $dest;
	//	echo "Trafo<br>";

	$white = imagecolorallocatealpha($dest,255,255,255,0);
	$black = imagecolorallocatealpha($dest,0,0,0,0);
	imagefilledellipse($dest, 15, 15, 18, 18, $black);
	imagefilledellipse($dest, 15, 15, 12, 12, getHVMastcolor($span[0]));
	imagefilledellipse($dest, 15, 15, 6, 6, $black);
}

function showknppcolors ($span) {
	global $dest;
	//	echo "Knooppunt<br>";

	$white = imagecolorallocatealpha($dest,255,255,255,0);
	$black = imagecolorallocatealpha($dest,0,0,0,0);
	$spancolor = getHVKnppcolor($span[0]);
	imagefilledrectangle($dest, 0, 0, 19, 1, $spancolor);
	imagefilledrectangle($dest, 0, 4, 19, 5, $spancolor);
	imagefilledrectangle($dest, 9, 6, 10, 24, $spancolor);
}

if ($type=='conv') {
	$height = (($nrsp * 15)+($nrsp-1));
	//	$height = ($nrsp * 14) + ($nrsp-1);
	$ty = ($height-38) * 0.5;
	if ($nrsp < 3) {$height = 38; $ty=0;};

	// hack because somehow GE has a bug with 3 voltages icons
	if ($nrsp == 3) {$height = 63;};

	$dest = imagecreatetruecolor(67, $height);
	// Transparent Background
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);

	// Copy and merge trafo symbol
	$src = imagecreatefrompng($IconDir.'part-conv.png');
	imagecopy($dest, $src, 28, $ty, 0, 0, 39, 38);
	imagedestroy($src);
	showcolors($nrsp, $span);
}

if ($type=='coil') {
	$height = (($nrsp * 15)+($nrsp-1));
	$ty = ($height-26) * 0.5;
	if ($nrsp < 2) {$height = 26; $ty=0;};
	// hack because somehow GE has a bug with 3 voltages icons
	if ($nrsp == 3) {$height = 63;};

	$dest = imagecreatetruecolor(54, $height);
	// Transparent Background
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);

	// Copy and merge trafo symbol
	$src = imagecreatefrompng($IconDir.'part-coil.png');
	imagecopy($dest, $src, 28, $ty, 0, 0, 26, 26);
	imagedestroy($src);
	showcolors($nrsp, $span);
}

if ($type=='trafo') {
	//echo "Trafo<br>";
	$height = (($nrsp * 15)+($nrsp-1));
	$ty = ($height-29) * 0.5;
	if ($nrsp < 3) {$height = 31; $ty=0;};

	// hack because somehow GE has a bug with 3 voltages icons
	if ($nrsp == 3) {$height = 63;};

	$dest = imagecreatetruecolor(63, $height);

	// Transparent Background
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);

	// Copy and merge trafo symbol
	$src = imagecreatefrompng($IconDir.'part-trafo.png');
	imagecopy($dest, $src, 30, $ty, 0, 0, 33, 30);
	imagedestroy($src);
	showcolors($nrsp, $span);
}
if ($type=='none') {
	if ($naam <> '' and $nrsp == 1) {
		$height = 25;
	} else {
		$height = ($nrsp * 15) + ($nrsp-1);
	}
	if ($naam<>'') {
		$font = 'Arial-Unicode-Bold.ttf';
		$type_space = imagettfbbox($font_size, 0, $font, $naam);
		$width = 36 + abs($type_space[4] - $type_space[0]);
	} else {
		$width = 63;
	}
	// hack because somehow GE has a bug with 3 voltages icons
	if ($nrsp == 3) {$height = 63;};
	$dest = imagecreatetruecolor($width, $height);
	// Transparent Background
	$white = imagecolorallocatealpha($dest,255,255,255,0);
	$black = imagecolorallocatealpha($dest,0,0,0,0);
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);
	if ($naam<>'') {
		$textcolor = getHVLblcolor($span[0]);
		if ($nrsp==1) {
			$yoffs =8+$font_size;
		} else {
			$yoffs = ($nrsp-1)*15-6+$font_size;
		}
		imagettftext($dest, $font_size, 0, 32, $yoffs, $textcolor, $font, $naam);
		}
	$scale = getIconScale('StationsIcoon',$span[0]);
	showcolors($nrsp, $span);
}

if ($type=='mast') {
	if ($naam<>'') {
		$font = 'arial.ttf';
		$type_space = imagettfbbox($font_size, 0, $font, $naam);
		$width = 32 + abs($type_space[4] - $type_space[0]);
	} else {
		$width = 32;
	}
	$dest = imagecreatetruecolor($width, 32);
	//	// Transparent Background
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);
	if ($naam<>'') {
		$textcolor = getHVMastLblcolor($span[0]);
		imagettftext($dest, $font_size, 0, 30, 25, $textcolor, $font, $naam);
	}
	showmastcolors($span);
}
 
if ($type=='knpp') {
	if ($naam<>'') {
		$font = 'arial.ttf';
		$type_space = imagettfbbox($font_size, 0, $font, $naam);
		$width = 26 + abs($type_space[4] - $type_space[0]);
	} else {
		$width = 20;
	}
	$dest = imagecreatetruecolor($width, 25);
	//	// Transparent Background
	imagealphablending($dest, false);
	$transparency = imagecolorallocatealpha($dest, 0, 0, 0, 127);
	imagefill($dest, 0, 0, $transparency);
	imagesavealpha($dest , true);
	if ($naam<>'') {
		$textcolor = getHVKnppLblcolor($span[0]);
		imagettftext($dest, $font_size, 0, 25, 22, $textcolor, $font, $naam);
	}
	showknppcolors($span);
}

// Output and free from memory

$scaledimg = imagescale($dest, intval(ceil((imagesx($dest) * $scale))));
if ($scaleIcon) {
	$scaledimg = imagescale($scaledimg, intval(ceil((imagesx($scaledimg)* 1.2))));
}
imagesavealpha($scaledimg , true);
//header('Content-Disposition: Attachment;filename=icon.png');
header('Content-Type: image/png');
imagepng($scaledimg,NULL,9);
imagedestroy($scaledimg);
?>