<!DOCTYPE html>
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

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}

// =========================== Access logging ===========================
$ref = @$_SERVER['HTTP_REFERER'];
$agent = @$_SERVER['HTTP_USER_AGENT'];
$ip = @$_SERVER['REMOTE_ADDR'];
$remdom = gethostbyaddr($ip);
$country = "unknown";
//$country = file_get_contents("http://ipinfo.io/{$ip}/country");
$tracking_page_name = __FILE__;
$method = $scriptnaam .' '. $scriptversie;

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) { die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), 'MySQL Not connected'));}

$strSQL = "INSERT INTO track (ref, agent, ip, domain, tracking_page_name, cntry, method) VALUES ('$ref','$agent','$ip','$remdom','$tracking_page_name','$country', '$method' )";
$test=mysqli_query($connection, $strSQL);
//Close connection to MySQL server
mysqli_close($connection);

// ======================== EINDE Access Logging =========================
?>
<html lang="nl">
<head>
	<title><?php echo vertaal($KaartTitel); ?></title>
	
	<meta name="robots" content="index, nofollow">
	<meta name="description" content="hoogspanningsnet.com, Hoogspanningsnetkaart, Netkaart, High Voltage Gridmap, Gridmap, Hochspannung netzkarte, Netzkarte, Carte du reseau Electrique Haute tension">
	<meta name="keywords" content="Hoogspanningsnet, Hoogspanningsnetkaart, High Voltage Gridmap, Hochspannungsnetzkarte, Carte Haute Tension, Netkaart, Gridmap, Netzkarte, Carte du Reseau">
	<meta name="author" content="http://www.hoogspanningsnet.com">
	
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	
	<link rel="shortcut icon" href="files/favicon.ico" />
	
	<!-- Load Font awesome -->
	<link  href="plugins/Font-Awesome/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
	
	<!-- Load Roboto slab -->
	<link href="plugins/Google-Roboto-Slab/css.css" rel="stylesheet"> 
	
	<!-- Load jQuery (for search control) -->
    <script src="plugins/jQuery/jquery-3.1.1.min.js"></script>
	
	<!-- Load jQuery Nice select (for sidebar) -->
	<script src="plugins/jquery-nice-select/js/jquery.nice-select.js"></script>
	<link  href="plugins/jquery-nice-select/css/nice-select.css" rel="stylesheet" />
	
	<!-- Leaflet kernel -->
	<link  href="plugins/Leaflet/Leaflet-1.0.3/leaflet.css" rel="stylesheet" />
	<script src="plugins/Leaflet/Leaflet-1.0.3/leaflet.js"></script>

	<!-- Leaflet spin Plugin -->
	<link  href="plugins/leaflet-loading/Control.Loading.css" rel="stylesheet" />
	<script src="plugins/leaflet-loading/Control.Loading.js"></script>
	
	<!-- URL Hash Plugin -->
    <script src="plugins/leaflethash/leaflethash.js"></script>
    
    <!-- Alert window -->
	<link  href="plugins/popup-window/L.Control.Window.css" rel="stylesheet" />
	<script src="plugins/popup-window/L.Control.Window.js"></script>

	<!-- Custom condensed attribute plugin -->
	<link  href="plugins/attrib/leaflet-control-condended-attribution.css" rel="stylesheet" />	
	<script src="plugins/attrib/leaflet-control-condended-attribution.js"></script>

	<!-- Locate Plugin -->
	<link  href="plugins/locate/L.Control.Locate.css" rel="stylesheet" />	
	<script src="plugins/locate/L.Control.Locate.min.js"></script>

	<!-- Sidebar Plugin -->
    <link  href="plugins/leaflet-sidebar/L.Control.Sidebar.css" rel="stylesheet" />
    <script src="plugins/leaflet-sidebar/L.Control.Sidebar.js"></script>

	<!-- Easy button Plugin -->
	<link  href="plugins/Leaflet-EasyButton/easy-button.css" rel="stylesheet" />
	<script src="plugins/Leaflet-EasyButton/easy-button.js"></script>

    <!-- Esri Leaflet plugin -->
    <script src="plugins/leaflet-Esri/esri-leaflet-v2.0.8/dist/esri-leaflet.js"></script>

	<!-- GeoJSON.Ajax plugin -->
	<script src="plugins/Leaflet.GeoJSON.Ajax/GeoJSON.Style.js"></script>
	<script src="plugins/Leaflet.GeoJSON.Ajax/GeoJSON.Ajax.js"></script>

	<!-- Search plugin -->
	<link  href="plugins/geoJSONAutocomplete/geojsonautocomplete.css" rel="stylesheet" />
	<script src="plugins/geoJSONAutocomplete/geojsonautocomplete.js"></script>

	<!-- Locate plugin -->
	<link  href="plugins/leaflet-locate/L.Control.Locate.min.css" rel="stylesheet" />
	<script src="plugins/leaflet-locate/L.Control.Locate.min.js"></script>

	<!-- Google plugin -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4bOBj2szTDv50hN8VgAo78itkCN92jik" async defer></script>

	<!-- Make sure the netkaart still works on IE11 -->
	<script src="plugins/es6-promise/es6-promise.min.js"></script>
	<script>ES6Promise.polyfill();</script>

	<!-- Google mutant layers plugin -->
	<script src="plugins/leaflet-GoogleMutant/Leaflet.GoogleMutant.js"></script>
	
	<!-- Settings -->
	<script src="NetkaartSettings.js"></script>

	<!-- Markers -->
	<script src="NetkaartMarkers.js"></script>
	
	<!-- Netkaart layers -->
	<script src="NetkaartLayers.js"></script>

	<!-- Netkaart stylesheet -->
	<link href="netkaart.css" rel="stylesheet"/>

    <!--[if lte IE 6]>
    <style type="text/css">
        #container {
            height: 100%;
        }
    </style>
    <![endif]-->
</head>

<body>
	<script type="text/javascript">
	function showSearchBar() {
		var x = document.getElementById('searchContainer');
	    x.style.display = 'block';
	}	
	</script>

	<div class="container" id="container">
		<!-- Topbar -->
		<div class="topbar" id="topbar">
			<div class="toptextleft">
				<!-- Shown search button -->
				<button class="netkaart-searchbutton fa fa-search" title="<?php echo vertaal('Zoeken in de netkaart')?>" onclick="showSearchBar()"></button>

				<!-- Div for Searchcontrol -->
				<div id='searchContainer'  style="position: fixed; display: none"></div>
			</div>
			<div class="toptextmiddle"><?php echo vertaal($KaartTitel) ?></div>
			<div class="toptextright"><?php echo vertaal('Versie').' '.$KaartVersie; ?>&nbsp;&nbsp;&nbsp;&nbsp;</div>
		</div>
	
		<!-- Map block -->
		<div class="wrapper" id="map"></div>
	</div>
	
	<!-- Div for Legendbar block -->
	<div id="legendbar"></div>

	<!-- Div for Sidebar block -->
	<div id="sidebar"></div>
	
	<!-- Div for Item block -->
	<div id="balloonbar"></div>

	<!-- Load the Netkaart application -->
	<script src="index.js"></script>
</body>
</html>