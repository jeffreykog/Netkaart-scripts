<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script the contents of the       */
/* balloons in GE 2nd version            */
/* Uses DIV's instead of tables          */
/* Automatically uses picture orientation*/
/* ===================================== */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');
require('hulpfuncties.php');
require_once($CommonFileDir.'class.translator.php');

if(isset($_COOKIE['NKformLanguage'])) {
	$translate = new Translator($_COOKIE['NKformLanguage']);
} else {
	$translate = new Translator('nl');
}

function vertaal($str){
	global $translate;
	$vert = $translate->__($str);
	return $vert;
}

function checkyears ($jaar){
	$val=$jaar;
	if ($jaar==0) {$val='Bouwjaar onbekend';}
	if ($jaar==9999) {$val='heden';}
	return $val;
}

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
	echo '<div class="geen data"><div class="omranding">';
	echo '<div class="header">';
	echo '<h1>'.vertaal("FOUT: Geen data gevonden").'</h1><img src="files/hsnet_transpa.png">';
	echo '</div>';
	echo '<div class="inhoud">';
	echo '<div class="foto-portrait"><img class="foto" src="files/fout_geenfoto_blank.png">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tekst;
	echo '</div></div>';
}

header('Content-Type: text/html; charset="UTF-8"');
echo '<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="balloon3.css?'.time().'"></head><body>';

if (isset($_GET['ID'])) {
	$editID = $_GET['ID'];
	$editTP = $editID[0];
	$editID = substr($editID,1);
}

if ($editTP == 'v') {
	$query = 'SELECT *, astext(linestring) as lijn, astext(ST_StartPoint(linestring)) as center  FROM Verbindingen WHERE ID="'.$editID.'"';
	$display = $VerbindingenDisplay;
} elseif ($editTP == 's') {
	$query = 'SELECT *, astext(point) as center FROM Stationsiconen WHERE ID="'.$editID.'"';
	$display = $StationsiconenDisplay;
} elseif ($editTP == 'm') {
	$query = 'SELECT *, X(point) as lon, Y(point) as lat, astext(point) as center FROM Masten WHERE ID="'.$editID.'"';
	$display = $MastenDisplay;
} elseif ($editTP == 't') {
	$query = 'SELECT *, astext(ST_Centroid(polygon)) as center FROM Stations WHERE ID="'.$editID.'"';
	$display = $StationsterreinDisplay;
} elseif ($editTP == 'k') {
	$query = 'SELECT *, astext(point) as center FROM Knooppunten WHERE ID="'.$editID.'"';
	$display = $KnooppuntenDisplay;
} elseif ($editTP == 'n') {
	$query = 'SELECT *, astext(point) as center FROM Netopeningen WHERE ID="'.$editID.'"';
	$display = $OpeningenDisplay;
} elseif ($editTP == 'b') {
	$query = 'SELECT *, astext(point) as center FROM Bedrijfsmiddelen WHERE ID="'.$editID.'"';
	$display = $BedrijfsmiddelenDisplay;
} else {
	printerror(vertaal("Geen correct gegevens type").".");
	die();
}

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__),'Not connected to MySQL')); }
mysqli_query($connection, "SET  character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
// Het data from record.
$gegevens = mysqli_query($connection, $query);
if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));}
$rij = mysqli_fetch_assoc($gegevens);
if (!$rij) { 
	printerror(vertaal("ID niet gevonden").".");
	die(); 
}

// Weergave functies
function GetOrientation($foturl){
	list($width, $height) = getimagesize($foturl);
	if ($width >= $height) {
		return 'landscape';
	} else {
		return 'portrait';
	}
}

function DisplayTitle ($tablerow, $disprow) {
	$retval = '<h1>';
	if ($disprow['Type']=='Func') {
		if ($tablerow[$disprow['Column']]=='Kabel') {
			$retval .= vertaal('Hoogspanningskabel');
		} elseif ($tablerow[$disprow['Column']]=='Lijn') {
			$retval .= vertaal('Hoogspanningslijn');
		};
	} else {
		$retval .= vertaal($disprow['DisplayNaam']);
	}
	$retval .= '</h1><img src="files/embleem_hoogspanningsnet_transpa.png">';
	return $retval;
}

function DisplayFoto ($type, $tablerow) {
	global	$FotoURLOK;
	
	$retval ='';
	if ($tablerow['FotoURL']=='' OR !$FotoURLOK) {  // FotoURL is blank or not valid
		if ($type=='v') {
			if ($tablerow['HoofdType']=='Kabel'){$retval .= '<img class="foto" src="files/grondkabel_geenfoto_blank.png">';}
			if ($tablerow['HoofdType']=='Lijn') {$retval .= '<img class="foto" src="files/luchtlijn_geenfoto_blank.png">';}
		}
		elseif ($type == 's') 					{$retval .= '<img class="foto" src="files/station_geenfoto_blank.png">';}
		elseif ($type == 'm') 					{$retval .= '<img class="foto" src="files/mastpositie_geenfoto_blank.png">';}
		elseif ($type == 't') 					{$retval .= '<img class="foto" src="files/station_geenfoto_blank.png">';}
		elseif ($type == 'k') 					{$retval .= '<img class="foto" src="files/knooppunt_geenfoto_blank.png">';}
		$retval .= '<h2>'.vertaal("GEEN FOTO BESCHIKBAAR").'</h2>';
	} else {			// FotoURL is not blank
		$retval .= '<a target="_blank" href="'.$tablerow['FotoURL'].'"><img class="foto" src="'.$tablerow['FotoURL'].'"></a>';
	}	
	return $retval;
}

function DisplayInbedrijf ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.vertaal(checkyears($tablerow[$velden[0]])).' - '.vertaal(checkyears($tablerow[$velden[1]])).'</div></div>';
}

function DisplayCircuits ($tablerow, $disprow){
	global $MastLijnAfstand;
	global $connection;
	
	if ($tablerow[$disprow['Column']]=='') {
		$query = "SET @Meters_Offset = ".strval($MastLijnAfstand);
		$gegevens = mysqli_query($connection, $query);
		$query = "SET @offy = @Meters_Offset / 111248";
		$gegevens = mysqli_query($connection, $query);
		$query = "SET @offx = @Meters_Offset / 73500";
		$gegevens = mysqli_query($connection, $query);
		$query = "SELECT Spanning, Naam from Verbindingen Where HoofdType='Lijn' AND ST_Intersects(linestring, Linestring(
					POINT(".$tablerow['lon']."-@offx, ".$tablerow['lat']."+@offy),
					POINT(".$tablerow['lon']."-@offx, ".$tablerow['lat']."-@offy),
					POINT(".$tablerow['lon']."+@offx, ".$tablerow['lat']."-@offy),
					POINT(".$tablerow['lon']."+@offx, ".$tablerow['lat']."+@offy),
					POINT(".$tablerow['lon']."-@offx, ".$tablerow['lat']."+@offy)))  
					AND JaarInBedrijf<=YEAR(CURDATE()) AND JaarUitBedrijf>=YEAR(CURDATE()) ORDER BY Spanning DESC, Naam ASC";
		$gegevens = mysqli_query($connection, $query);
		$temp = '';
		while($verbing = mysqli_fetch_array($gegevens)) {
			$temp = $temp . $verbing['Naam'] . "<br>";
		}
		return '<div class="bigtitel">'.vertaal("Circuits").'</div><div class="bigwaarde">'.$temp .'</div>';
	} else {
		$delimiters = ' .,;-|\\/';
		$lijnen = rtrim($tablerow[$disprow['Column']], $delimiters);
		$delimiters = str_split($delimiters);
		$lijnen = str_replace($delimiters,', ',$lijnen);
		$lijnen = str_replace('v','',$lijnen);
		$query	= "SELECT Naam from Verbindingen WHERE ID IN (".$lijnen.") ORDER BY Spanning DESC, Naam ASC";
		$gegevens = mysqli_query($connection, $query);
		$temp = '';
		while($verbing = mysqli_fetch_array($gegevens)) {
			$temp = $temp . $verbing['Naam'] . "<br>";
		}
		return '<div class="bigtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="bigwaarde">'.$temp.'</div>';
	}
}

function DisplayOpmerkingen ($tablerow, $disprow) {
	return '<div class="opmerkingen"><div class="opmtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="opmwaarde">'.$tablerow[$disprow['Column']].'</div></div>';
}

function DisplayLengte ($tablerow, $disprow) {
	$lengte = number_format(greatCircleLength($tablerow['lijn'])/1000, 2, ',', '.');
	return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.$lengte.' '.$disprow['Suffix'].'</div></div>';;
}

function DisplaySpanning ($tablerow, $disprow) {
	$spanning = SpanningDecimals($tablerow[$disprow['Column']]);
	return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.$spanning.' '.$disprow['Suffix'].'</div></div>';;
}

function DisplayVerbSubtype ($tablerow, $disprow) {
	if ($tablerow['HoofdType']=='Kabel') {
		return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.vertaal($tablerow[$disprow['Column']]).vertaal("kabel").'</div></div>';
	} elseif ($tablerow['HoofdType']=='Lijn') {
		return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.$tablerow[$disprow['Column']].'</div></div>';
	}
}

function DisplayTerrSubtype ($tablerow, $disprow) {
	return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.$tablerow['HoofdType'].' - '.$tablerow[$disprow['Column']].'</div></div>';
}

function DisplayVerbDeelnet ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	return '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="gegwaarde">'.$tablerow[$velden[0]].' - '.$tablerow[$velden[1]].'</div></div>';
}
function DisplayValues ($type, $tablerow, $disprow) {
	$retval = '<div class="gegevens">';
	for ($x = 1; $x < count($disprow)-1; $x++) {
		if ($disprow[$x]['Type']=='Small') {
			$retval .= '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="gegwaarde">'.$tablerow[$disprow[$x]['Column']].' '.$disprow[$x]['Suffix'].'</div></div>';
		} elseif ($disprow[$x]['Type']=='Big') {
			$retval .= '<div class="gegevensbig"><div class="bigtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="bigwaarde">'.$tablerow[$disprow[$x]['Column']].'</div></div>';
		} elseif ($disprow[$x]['Type']=='Func') {
			$func = $disprow[$x]['FuncName'];
			$retval .= $func($tablerow, $disprow[$x]);
		} elseif ($disprow[$x]['Type']=='Link') {
			$retval .= '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="gegwaarde"><a href="edititem.php?ID='.$_GET['ID'].'">'.$_GET['ID'].'</a></div></div>';
		} elseif ($disprow[$x]['Type']=='Blanco') {
			$retval .= '<div class="gegevensrij"><div class="gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="gegwaarde"></div></div>';
		}
	}
	return $retval;
}

//Mainloop ==========
$center = "ST_GeomFromText('".$rij['center']."')";
$ip = "INET6_ATON('".@$_SERVER['REMOTE_ADDR']. "')";
$query = @$_GET['ID'];
$zoom = 0;
$viewSQL = "INSERT INTO views (ip, center, hoogte, query, accestype) VALUES ($ip, $center,'$zoom','$query', 'GB')";
//	echo $viewSQL . '\n';
$test=mysqli_query($connection, $viewSQL);
if (!$test) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query')); }

$FotoURLOK = isValidImageURL($rij['FotoURL']);
if ($FotoURLOK) {
	$orientation = GetOrientation($rij['FotoURL']);
} else {
	$orientation= 'portrait';
}
echo '<div class="omranding">';
	echo '<div class="header">';
		echo DisplayTitle ($rij, $display[0]);
	echo '</div>';  // div header
	echo '<div class="inhoud">';
		echo '<div class="foto-'.$orientation.'">';
			echo DisplayFoto($editTP, $rij);
			echo DisplayValues($editTP, $rij, $display);
		if ($orientation=='landscape') {	
			echo '</div>'; // div foto-
			echo '</div>'; //gegevens
			echo DisplayOpmerkingen($rij, end($display));
		} else {
			echo DisplayOpmerkingen($rij, end($display));
			echo '</div>'; //gegevens
			echo '</div>'; // div foto-
		}
	echo '</div>'; // div inhoud
echo '</div>'; // div omranding

//Close connection to MySQL server
mysqli_close($connection);
echo '</body></html>';
?>