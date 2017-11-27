<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the country KML */
/* Which contains verbindingen,          */
/* stationsiconen,stationsmarkeringen    */
/* It is called evry time the user       */
/* changes his view in GE                */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$time_start = microtime(true);

// Get some meory case this script if memory intensive
ini_set('memory_limit', '512M');

// Load files we require to run this script
require('phpsqlajax_dbinfo.php');
require('verbindingen.php');
require('terreinmarkeringen.php');
require('knooppunten.php');
require('netopeningen.php');
require('stations.php');
require('masten.php');

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) { die(LogMySQLError(mysqli_connect_error(), basename(__FILE__),'Not connected to MySQL')); }

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">';
$kml[] = '<Document>';
// Get the latest database update and put this and other info in the KML file first.
$query = 'SELECT max( tijd ) as tijd FROM `wijzigingen` WHERE 1';
$result = mysqli_query($connection, $query);
if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query')); }
while ($rij = @mysqli_fetch_assoc($result)) {
  $temp = array('GenerationDate' => date('c'), 'LatestDatabaseUpdate' => $rij['tijd'], 'ScriptName' => $scriptnaam, 'ScriptVersion' => $scriptversie, 'Copyrights' => $copyrightstring);
}
VoegDataIn($temp);

// Read the file with styles (it is generated once by calling styles.php)
 $kml[] = file_get_contents($StyleFile);

// Get the commandline parameters, the viewbox (BBOX), the height (CAMERA) and the expertmode (EXPERT)
$bbox = $_GET['BBOX'];
list($bbox, $range, $expert) = explode(";", $bbox);

$range = intval(str_replace('CAMERA=','',$range));
$expert = strtolower(str_replace('EXPERT=','',$expert));
list($bbox_west, $bbox_south, $bbox_east, $bbox_north) = explode(",", $bbox);
$sqlbox = 'LINESTRING(' . $bbox_west . ' ' . $bbox_south . ',' . $bbox_east . ' ' . $bbox_north . ')';

// =========================== View logging ===========================
$center = "ST_GeomFromText('POINT(". strval((floatval($bbox_east)+floatval($bbox_west))/2) ." ". strval((floatval($bbox_north)+floatval($bbox_south))/2) . ")')";
$ip = "INET6_ATON('".@$_SERVER['REMOTE_ADDR']. "')";
$query = @$_SERVER['QUERY_STRING'];
$viewSQL = "INSERT INTO views (ip, center, hoogte, query, accestype) VALUES ($ip, $center,'$range','', 'G')";
//	echo $viewSQL . '\n';
$test=mysqli_query($connection, $viewSQL);
if (!$test) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query')); }

// Selects all the countries with entries in the verbindingen table.
$query = 'SELECT Land FROM Verbindingen WHERE 1 GROUP BY Land ORDER BY ID';
$result = mysqli_query($connection, $query);
if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query')); }

//echo 'breeek---->';

// For every country generate the map.
while ($rij = @mysqli_fetch_assoc($result)) {
	$kml[] = '<Folder>';
	$kml[] = 	'<name>Netkaart '. $rij['Land'] . '</name>';
	TekenVerbindingen( $rij['Land'] );
	TekenTerreinMarkeringen( $rij['Land'] );
	TekenKnooppunten( $rij['Land'] );
	TekenNetopeningen( $rij['Land'] );
	TekenStationsIconen( $rij['Land'] );
	TekenMasten( $rij['Land'] );
	$kml[] = '</Folder>';
}
//close database
mysqli_close($connection);

// End XML file
if ($PerformanceTiming==true) {
	$kml[] = '<Folder>';
	$TimeGen = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
	$kml[] = '<name>Tijd '. $TimeGen . 's</name>';
	$kml[] = '</Folder>';
}
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);

// Spit out headers identifing this as a google earth file so the browser opens the right application 
header('Content-type: application/vnd.google-earth.kmz' );
//header('Content-Disposition: attachment; filename="netkaart.kmz"');
if ($expert == 'true') {
	header('Content-Disposition: attachment; filename="'.date('d-m-Y').'-netkaart.kmz"');
} else {
	header('Content-Disposition: attachment; filename="netkaart.kmz"');
}
// Make a randomly named KMZ (zipped) file and put the KML array in it.
$kmzfile = 'temp/'.generateRandomString().'.kmz';
$zip = new ZipArchive();
if ($zip->open($kmzfile, ZIPARCHIVE::CREATE)!==TRUE) {	exit("cannot open <$kmzfile>\n"); }
$zip->addFromString("doc.kml", $kmlOutput);

// When in expert mode add all the png files in the files directory to the KMZ
if ($expert == 'true') {
	foreach(glob('files/*.png') as $file) {
		$zip->addFile($file);
	}
}
$zip->close();
$ZipGrootte=filesize($kmzfile);

// Send the KMZ file to the user and delete the temporary file
echo file_get_contents($kmzfile);
//sleep(4);
unlink($kmzfile);
if ($PerformanceTiming==true) {
	$TimeTot = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
	$load = sys_getloadavg();
	$LogLine = $_SERVER["REQUEST_TIME_FLOAT"] . ';'. $_SERVER["REMOTE_ADDR"]. ';' . $bbox_west . ';' . $bbox_south . ';' . $bbox_east . ';' . $bbox_north . ';' . 
						$range . ';' . $ZipGrootte . ';' . $TimeGen . ';' . $TimeTot . ';' . $load[0] . ';' . $load[1] . ';' . $load[2] . "\n";
	file_put_contents($PerformanceFile , $LogLine , FILE_APPEND | LOCK_EX);
}

?>