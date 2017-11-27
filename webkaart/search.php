<?php
/* ===================================== */
/* Copyright 2017, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script searches our netkaart     */
/* tabels for a match, returns match if  */
/* one is found, does a geo-database     */
/* is none is found                      */
/*                                       */
/* ===================================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_GET['search']) or empty($_GET['search']))
	die( json_encode(array('ok'=>0, 'errmsg'=>'specify search parameter') ) );

if(!isset($_GET['limit']) or empty($_GET['limit']))
	$_GET['limit']=10;
	
// Load files we require to run this script
require('phpsqlajax_dbinfo.php');
include_once('plugins/geoPHP/geoPHP.inc');

function searchFeatures($text, $limit)	//search initial text in titles
{
	global $connection;
	$geojson = array('type' => 'FeatureCollection', 'features'  => array());
	$query = "(SELECT CONCAT(Naam,' \(',Spanning,' kV\)') as title, 'Station.png' as image, Hoofdtype as description, Naam as popupContent, x(point) as x, y(point) as y FROM `Stationsiconen` WHERE `Naam` LIKE '".$text."%')".
		" UNION ALL ".
		"(SELECT CONCAT(Naam,' \(',Spanning,' kV\)') as title, 'Terrein.png' as image, 'Stationsterrein' as description, Naam as popupContent, x(ST_Centroid(polygon)) as x, y(ST_Centroid(polygon)) as y FROM `Stations` WHERE `Naam` LIKE '".$text."%')".
		" UNION ALL ".
		"(SELECT Naam as title, 'Verbinding.png' as image, HoofdType as description, Naam as popupContent, x(StartPoint(linestring)) as x, y(StartPoint(linestring)) as y FROM `Verbindingen` WHERE `Naam` LIKE '%".$text."%')".
		" UNION ALL ".
		"(SELECT Naam as title, 'Knooppunt.png' as image, 'Knooppunt' as description, Naam as popupContent, x(point) as x, y(point) as y FROM `Knooppunten` WHERE `Naam` LIKE '".$text."%')".
		" UNION ALL ".
		"(SELECT Naam as title, 'Mast.png' as image, 'Mast' as description, Naam as popupContent, x(point) as x, y(point) as y FROM `Masten` WHERE `Naam` LIKE '%".$text."%')".
		" LIMIT ".$limit;
	$result = mysqli_query($connection, $query);
	if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query')); }
	
	if (mysqli_num_rows ($result)==0) {
		require_once('plugins/php-nomatim/NominatimAPI.php');
		$api = new NominatimAPI();
		$result = $api->search($text);
		foreach ($result as $row) {
//			print_r($row);
			$feature = array(
					'type' => 'Feature',
					'geometry' => array(
							'type' => 'Point',
							'coordinates' => array(
									floatval($row->lon),
									floatval($row->lat)
							)
					),
					'properties' => array(
							'title' 		=> $row->display_name,
							'image' 		=> 'Geo_result.png',
							'description' 	=> 'Geo result',
							'popupContent' 	=> $row->display_name
					)
			);
			# Add feature arrays to feature collection array
			array_push($geojson['features'], $feature);
		}
	} else {
	# Loop through rows to build feature arrays
		while ($row = mysqli_fetch_assoc($result)) {
			$properties = $row;
//			$properties['title'] = utf8_decode($properties['title']);
//			$properties['popupContent'] = utf8_decode($properties['popupContent']);
			$feature = array(
				'type' => 'Feature',
				'geometry' => array(
					'type' => 'Point',
					'coordinates' => array(
						floatval($row['x']),
						floatval($row['y'])
					)
				),
				'properties' => $properties
			);
			# Add feature arrays to feature collection array
			array_push($geojson['features'], $feature);
		}
	}	
	return $geojson;
}

# Connect to MySQL database
// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), 'Not connected')); }
mysqli_set_charset ($connection, "utf8");

$fdata = searchFeatures($_GET['search'], $_GET['limit']);	//filter data
//print_r($fdata);
//Close connection to MySQL server
mysqli_close($connection);

$JSON = json_encode($fdata);




@header("Content-type: application/json; charset=utf-8");

if(isset($_GET['callback']) and !empty($_GET['callback']))	//support for JSONP request
	echo $_GET['callback']."($JSON)";
else
	echo $JSON;	//AJAX request
?>
