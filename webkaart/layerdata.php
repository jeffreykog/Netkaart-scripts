<?php
/*
* Title:   	MySQL Data to GeoJSON
* Notes:   	Query a MySQL table and return the results in GeoJSON format, suitable for use in OpenLayers, Leaflet, etc.
* Author:  	Bas van Duijnhoven Hoogspanningsnet.com
* Based on: code from Bryan R. McBride, GISP
* 			Contact: bryanmcbride.com
* 			GitHub:  https://github.com/bmcbride/PHP-Database-GeoJSON
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit','256M');
$start = microtime(true);

// Load files we require to run this script
require('phpsqlajax_dbinfo.php');
include_once('plugins/geoPHP/geoPHP.inc');

function wkb_to_json($wkb) {
	$geom = geoPHP::load($wkb,'wkb');
	return $geom->out('json');
}

function utf8_encode_all($dat) // -- It returns $dat encoded to UTF8
{
	if (is_string($dat)) return utf8_encode($dat);
	if (!is_array($dat)) return $dat;
	$ret = array();
	foreach($dat as $i=>$d) $ret[$i] = utf8_encode_all($d);
	return $ret;
}


$spanning = '380';
$type = 'verb';
$zoom = 6;
$sql = '';

if(isset($_GET['bbox'])){
	list($bbox_west, $bbox_south, $bbox_east, $bbox_north) = explode(",", $_GET['bbox']);
 	$sqlbox = 'LINESTRING(' . $bbox_west . ' ' . $bbox_south . ',' . $bbox_east . ' ' . $bbox_north . ')';
}
if (isset($_GET['country'])) {
	$country = $_GET['country'];
}
if (isset($_GET['voltage'])) {
	$spanning = $_GET['voltage'];
}
if (isset($_GET['zoom'])) {
	$zoom = round(floatval($_GET['zoom']));
}
# Build SQL SELECT statement
if (isset($_GET['type'])) {
	$type = $_GET['type'];
	if ($type=='verb') {
		if ($zoom > $MVNetZoomLevel) {
			// Geef alle verbindingen weer
			$sql = 'SELECT concat("v",ID) as ID,HoofdType,Spanning, aswkb(linestring) as wkb FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .
						'" AND JaarUitBedrijf>="'. intval(date("Y")) .
						'" AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning ASC, Naam ASC';
		} elseif ($zoom == $MVNetZoomLevel) {
			// Geef alle verbindingen met een spanning groter dan of gelijk aan $MVVoltagesMin weer
			$sql = 'SELECT concat("v",ID) as ID,HoofdType,Spanning, aswkb(linestring) as wkb FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .
						'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $MVVoltagesMin . 
						' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning ASC, Naam ASC';
		} elseif ($zoom < $MVNetZoomLevel AND $zoom > $HVNetZoomLevel) {
			// Geef alle verbindingen met een spanning groter dan of gelijk aan $HVVoltagesMin weer
			$sql = 'SELECT concat("v",ID) as ID,HoofdType,Spanning, aswkb(linestring) as wkb FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .
						'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $HVVoltagesMin . 
						' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning ASC, Naam ASC';
		} elseif ($zoom <= $HVNetZoomLevel) {
			// Geef alle verbindingen met een spanning groter dan of gelijk aan $EHVVoltagesMin weer
			$sql = 'SELECT concat("v",ID) as ID,HoofdType,Spanning, aswkb(linestring) as wkb FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .
						'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $EHVVoltagesMin . 
						' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning ASC, Naam ASC';
		}
	}
	if ($type=='stat') {
		if ($zoom > $MVIconZoomLevel) {
			// Geef alle verbindingen weer
			$sql = 'SELECT concat("s",ID) as ID,HoofdType, Spanning, Spanningen, Naam, aswkb(point) as wkb FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .
					'" AND JaarUitBedrijf>="'. intval(date("Y")) .
					'" AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning ASC, Spanningen ASC, Naam ASC';
			} elseif ($zoom == $MVIconZoomLevel) {
				// Geef alle verbindingen met een spanning groter dan of gelijk aan $MVVoltagesMin weer
				$sql = 'SELECT concat("s",ID) as ID,HoofdType,Spanning, Spanningen, Naam, aswkb(point) as wkb FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .
				'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $MVIconMin .
				' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning ASC, Spanningen ASC, Naam ASC';
			} elseif ($zoom < $MVIconZoomLevel AND $zoom >= $HVIconZoomLevel) {
			// Geef alle verbindingen met een spanning groter dan of gelijk aan $MVVoltagesMin weer
			$sql = 'SELECT concat("s",ID) as ID,HoofdType,Spanning, Spanningen, Naam, aswkb(point) as wkb FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .
					'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $HVIconMin .
					' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning ASC, Spanningen ASC, Naam ASC';
		} elseif ($zoom < $HVIconZoomLevel AND $zoom >= $EHVIconZoomLevel) {
			// Geef alle verbindingen met een spanning groter dan of gelijk aan $HVVoltagesMin weer
			$sql = 'SELECT concat("s",ID) as ID,HoofdType,Spanning, Spanningen, Naam, aswkb(point) as wkb FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .
					'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Spanning>='. $EHVIconMin .
					' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning ASC, Spanningen ASC, Naam ASC';
		} elseif ($zoom < $EHVIconZoomLevel) {
			$sql = '';
		}

//$sql = 'SELECT concat("s",ID) as ID,HoofdType,Spanning,Spanningen,Naam, aswkb(point) as wkb FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land IN '. $land .' AND MBRContains(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning DESC, Spanningen ASC, Naam ASC';
		
	}
	if ($type=='terr') {
		if ($zoom >= $TerrZoomLevel){
			$sql = 'SELECT concat("t",ID) as ID,HoofdType,Spanning, aswkb(polygon) as wkb FROM Stations WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stations.polygon) ORDER BY Spanning DESC, Naam ASC';
		} 
	}
	if ($type=='mast') {
		if ($zoom >= $MastZoomLevel){
			$sql = 'SELECT concat("m",ID) as ID,HoofdType,Spanning,Naam, aswkb(point) as wkb FROM Masten WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND MBRContains(GeomFromText("'.$sqlbox.'"), Masten.point) ORDER BY Spanning DESC, Naam ASC';
		} 
	}
	if ($type=='knpp') {
		if ($zoom >= $KnppZoomLevel){
			$sql = 'SELECT concat("k",ID) as ID,Spanning,Naam,IconPNG, aswkb(point) as wkb FROM Knooppunten WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND MBRContains(GeomFromText("'.$sqlbox.'"), Knooppunten.point) ORDER BY Spanning DESC, Naam ASC';
		} 
	}
}	

//echo $sql.'<br>';

# Build GeoJSON feature collection array
$geojson = array(
		'type' => 'FeatureCollection',
		'features' => array()
);

function reduceJsonPrecision($wkb, $zm) {
	$prec=6;
	if ($zm <= 4) {$prec=1;}
	elseif ($zm <= 6) {$prec=2;}
	elseif ($zm <= 9) {$prec=3;}
	elseif ($zm <= 11) {$prec=4;}
	elseif ($zm <= 13) {$prec=5;}
	
	$jsonarr=json_decode(wkb_to_json($wkb),true);
//	print_r($jsonarr);
	if ($jsonarr['type'] == 'LineString') {
		foreach ($jsonarr['coordinates'] as &$coor) {
			$coor[0]= number_format ($coor[0], $prec);
			$coor[1]= number_format ($coor[1], $prec);
		}
	} elseif ($jsonarr['type'] == 'Polygon') {
		foreach ($jsonarr['coordinates'] as &$poly) {
			foreach ($poly as &$coor) {
				$coor[0]= number_format ($coor[0], $prec);
				$coor[1]= number_format ($coor[1], $prec);
			}
		}
	} elseif  ($jsonarr['type'] == 'Point') {
		$jsonarr['coordinates'][0] = number_format ($jsonarr['coordinates'][0], $prec);
		$jsonarr['coordinates'][1] = number_format ($jsonarr['coordinates'][1], $prec);
	}
	return $jsonarr;
}

# Try query or error
if ($sql<>''){ 
	# Connect to MySQL database
	// Opens a connection to a MySQL server.
	$connection = mysqli_connect ($server, $username, $password, $database);
	if (!$connection) { die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), 'MySQL Not connected'));}

//execute query
// =========================== View logging ===========================
	if ($type=='verb') {
		$hoogtes = array ('100000000', '40000000', '150000000', '10000000', '4500000', '2700000', '1275000', '675000', '330000', '165000', '78000', '41000', '21000', '9900', '4900', '2300', '1260', '604', '295');
		$center = "ST_GeomFromText('POINT(". strval((floatval($bbox_east)+floatval($bbox_west))/2) ." ". strval((floatval($bbox_north)+floatval($bbox_south))/2) . ")')";
		$ip = "INET6_ATON('".@$_SERVER['REMOTE_ADDR']. "')";
		$query = @$_SERVER['QUERY_STRING'];
		$viewSQL = "INSERT INTO views (ip, center, hoogte, query, accestype) VALUES ($ip, $center, $hoogtes[$zoom], null, 'W')";
//	echo $viewSQL . '\n';
		$test=mysqli_query($connection, $viewSQL);
		if (!$test) { die(LogMySQLError(mysqli_error($connection).' '.$hoogtes[$zoom], basename(__FILE__), 'Invalid query')); }
	}
	
	$rs = mysqli_query($connection, $sql);
	if (!$rs) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query')); }
	//Close connection to MySQL server
	mysqli_close($connection);

	# Loop through rows to build feature arrays
	while ($row = mysqli_fetch_assoc($rs)) {
		$properties = $row;
		# Remove wkb and geometry fields from properties
		unset($properties['wkb']);
		$jsonreduced = reduceJsonPrecision($row['wkb'], $zoom);
		$feature = array(
			'type' => 'Feature',
			'geometry' =>$jsonreduced,
			'properties' => $properties
		);
		# Add feature arrays to feature collection array
		array_push($geojson['features'], $feature);
	}
}
header('Content-type: application/json');
// echo json_encode($geojson, JSON_NUMERIC_CHECK);
$json = utf8_encode_all($geojson);
echo json_encode($json);
$conn = NULL;
$time_elapsed = microtime(true) - $start;
// echo '\nTotal Execution Time: '.$time_elapsed_us.' sec';
?>
