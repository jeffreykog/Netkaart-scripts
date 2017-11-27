<?php
/* ===================================== */
/* Copyright 2017, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the captcha-    */
/* image for the feedback form           */
/* Adapted from http://99webtools.com/   */
/* blog/php-simple-captcha-script/       */    
/* ===================================== */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Generates captcha image and sets sesssion
// adapted from http://99webtools.com/blog/php-simple-captcha-script/

require('phpsqlajax_dbinfo.php');
putenv('GDFONTPATH=' . realpath($CommonFileDir));

session_start();
$code=strval(rand(1000,9999));
$_SESSION["code"]=$code;

$im = imagecreatetruecolor(70, 23);
$bg = imagecolorallocate($im, 205, 230, 69); //background color blue
$fg = imagecolorallocate($im, 102, 102, 102);//text color white
imagefill($im, 0, 0, $bg);
$font = 'arial-bold.ttf';

for ($i=0; $i<=3; $i++) {
	$size = rand(10,20);
	$angle = rand(0,60)-30;
	$y = 20;
	imagettftext($im, $size, $angle, 7+(15*$i), $y, $fg, $font, $code[$i]);
}

header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/png');
imagepng($im);
imagedestroy($im);
?>