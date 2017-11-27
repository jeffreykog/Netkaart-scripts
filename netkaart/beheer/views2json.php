<?php
/*
* Title:   	Views Data to JSON
* Notes:   	Query a MySQL table and return the resultspoints in JSON format, suitable for use in OpenLayers, Leaflet, etc.
* Author:  	Bas van Duijnhoven Hoogspanningsnet.com
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit','512M');
$start = microtime(true);

// Load files we require to run this script
require('../phpsqlajax_dbinfo.php');

$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) { die('MySQL Not connected');}
$sql = "SELECT ST_X(center) AS Xcoord, ST_Y(center) AS Ycoord, INET6_NTOA(ip) as user, DATE_FORMAT(tm,'%d %b %Y %T:%f') as tm FROM views WHERE tm>=DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND (ip=inet6_ATON('188.102.233.67') OR ip=INET6_ATON('163.158.195.0')) AND hoogte<100000 ORDER by tm ASC";
$rs = mysqli_query($connection, $sql);
$qt = microtime(true);

if ($rs) { 
	$punten = array();
	# Loop through rows to build feature arrays
	while ($row = mysqli_fetch_assoc($rs)) {
		$punt = array (array (floatval($row['Ycoord']), floatval($row['Xcoord'])),array(utf8_encode($row['user']),$row['tm']));
//		print_r($punt);
		array_push($punten, $punt);
	}
}
$at = microtime(true);
//print_r($punten);
//Close connection to MySQL server
mysqli_close($connection);
ini_set('precision', 8);
echo json_encode($punten, JSON_UNESCAPED_UNICODE);
$jt = microtime(true);

//Echo '\nTime in query:'. ($qt-$start) . 'miccosecs.\n';
//Echo '\nTime in arrayloop:'. ($at-$qt) . 'microsecs.\n';
//Echo '\nTime in json and echo:'. ($jt-$at) . 'microsecs.\n';

?>