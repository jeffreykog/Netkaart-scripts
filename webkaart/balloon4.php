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

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
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
	echo '<div class="header">';
		echo '<h1>'.vertaal("FOUT: Geen data gevonden").'</h1>';
	echo '</div>';
		echo '<div class="foto-portrait"><img class="foto" src="files/fout_geenfoto_blank.png">';
	echo '</div>';
	echo '<div class="gegevens">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tekst;
	echo '</div>';
}

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
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), vertaal('Er kan op dit moment geen verbinding gemaakt worden met de netkaart-database, probeer het over 20 seconden normaals')).'.'); }
// Het data from record.
$gegevens = mysqli_query($connection, $query);
if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), vertaal('Foute query')));}
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
//	$retval = '<h1><a href="edititem.php?ID='.$_GET['ID'].'">';
	$retval = '<h1><a id="balloon-edititem" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;">';
	$retval .= vertaal($disprow['DisplayNaam']);
	$retval .= '</a></h1>';
	return $retval;
}

function DisplayFoto ($type, $tablerow) {
	global $FotoURLOK;
	
	$retval ='';
	if ($tablerow['FotoURL']=='' OR !$FotoURLOK) {  // FotoURL is blank or invalid
		if ($type=='v') {
			if ($tablerow['HoofdType']=='Kabel'){$retval .= '<img class="balloon-foto" src="files/grondkabel_geenfoto_blank.png">';}
			if ($tablerow['HoofdType']=='Lijn') {$retval .= '<img class="balloon-foto" src="files/luchtlijn_geenfoto_blank.png">';}
		}
		elseif ($type == 's') 					{$retval .= '<img class="balloon-foto" src="files/station_geenfoto_blank.png">';}
		elseif ($type == 'm') 					{$retval .= '<img class="balloon-foto" src="files/mastpositie_geenfoto_blank.png">';}
		elseif ($type == 't') 					{$retval .= '<img class="balloon-foto" src="files/station_geenfoto_blank.png">';}
		elseif ($type == 'k') 					{$retval .= '<img class="balloon-foto" src="files/knooppunt_geenfoto_blank.png">';}
		$retval .= '<h2 class="balloon">'.vertaal("GEEN FOTO BESCHIKBAAR").'</h2>';
	} else {			// FotoURL is  blank
		$retval .= '<a target="_blank" href="'.$tablerow['FotoURL'].'" class="balloon-image-link"><img class="balloon-foto" src="'.$tablerow['FotoURL'].'"></a>';
	}	
	return $retval;
}

function DisplayLandnaam ($tablerow, $disprow) {
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal(country_code_to_country($tablerow[$disprow['Column']])).'</div></div>';
}

function DisplayInbedrijf ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal(checkyears($tablerow[$velden[0]])).' - '.vertaal(checkyears($tablerow[$velden[1]])).'</div></div>';
}

function DisplaySpanning ($tablerow, $disprow) {
	$spanning = SpanningDecimals($tablerow[$disprow['Column']]);
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$spanning.' '.$disprow['Suffix'].'</div></div>';
}


function DisplayOntwerpSpan ($tablerow, $disprow) {
	if ($tablerow[$disprow['Column']]!=0 AND $tablerow[$disprow['Column']]<>$tablerow['Spanning']) {
		$spanning = SpanningDecimals($tablerow[$disprow['Column']]);
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$spanning.' '.$disprow['Suffix'].'</div></div>';
	}
}

function DisplayMastHoogte ($tablerow, $disprow) {
	if ($tablerow[$disprow['Column']]!=0) {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$tablerow[$disprow['Column']].' '.$disprow['Suffix'].'</div></div>';
	}
}

function DisplayHoogsteSpan ($tablerow, $disprow) {
	if ($tablerow[$disprow['Column']]!=0) {
		$spanning = SpanningDecimals($tablerow[$disprow['Column']]);
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$spanning.' '.$disprow['Suffix'].'</div></div>';
	} else {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal('onbekend').'</div></div>';
	}
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
		if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), vertaal('Foute query')));}
		$temp = '';
		while($verbing = mysqli_fetch_array($gegevens)) {
			$temp = $temp . $verbing['Naam'] . "<br>";
		}
		return '<div class="balloon-bigtitel">'.vertaal("Circuits").'</div><div class="balloon-bigwaarde">'.utf8_encode($temp).'</div>';
	} else {
		$delimiters = ' .,;-|\\/';
		$lijnen = rtrim($tablerow[$disprow['Column']], $delimiters);
		$delimiters = str_split($delimiters);
		$lijnen = str_replace($delimiters,', ',$lijnen);
		$lijnen = str_replace('v','',$lijnen);
		$query	= "SELECT Naam from Verbindingen WHERE ID IN (".$lijnen.") ORDER BY Spanning DESC, Naam ASC";
		$gegevens = mysqli_query($connection, $query);
		if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), vertaal('Foute query')));}
		$temp = '';
		while($verbing = mysqli_fetch_array($gegevens)) {
			$temp = $temp . $verbing['Naam'] . "<br>";
		}
		return '<div class="balloon-bigtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-bigwaarde">'. utf8_encode($temp).'</div>';
	}
}

function DisplayOpmerkingen ($tablerow, $disprow) {
	return '<div class="opmerkingen"><div class="balloon-opmtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-opmwaarde">'.utf8_encode($tablerow[$disprow['Column']]).'</div></div>';
}

function DisplayLengte ($tablerow, $disprow) {
	$lengte = number_format(greatCircleLength($tablerow['lijn'])/1000, 2, ',', '.');
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$lengte.' '.$disprow['Suffix'].'</div></div>';;
}

function DisplayVelden ($tablerow, $disprow) {
	global $VeldTypen;
	
	foreach ($VeldTypen as $veldtype) {
		$tablerow[$disprow['Column']] = str_replace($veldtype,vertaal($veldtype),$tablerow[$disprow['Column']]);
	}
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.nl2br($tablerow[$disprow['Column']]).'</div></div>';;
	
}

function DisplayVerbHoofdtype ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	if ($tablerow[$velden[0]]=='Kabel') {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal($tablerow[$velden[1]].'kabel').'</div></div>';
	} elseif ($tablerow[$velden[0]]=='Lijn') {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal($tablerow[$velden[0]]).'</div></div>';
	}
}

function DisplaySysteemFreq ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	$titels = explode (',', $disprow['DisplayNaam']);
	$temp =	'<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($titels[0]).'</div><div class="balloon-gegwaarde">'.vertaal($tablerow[$velden[0]]).' - '.vertaal($tablerow[$velden[1]]).'</div></div>';
	if ($tablerow[$velden[2]] != '') {
		$temp .= '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($titels[1]).'</div><div class="balloon-gegwaarde">'.number_format($tablerow[$velden[2]], 1, ',', '.').' '.$disprow['Suffix'].'</div></div>';
	}	
	return $temp;
}

function DisplayTerrSubtype ($tablerow, $disprow) {
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal($tablerow['HoofdType']).' - '.$tablerow[$disprow['Column']].'</div></div>';
}

function DisplayVerbDeelnet ($tablerow, $disprow) {
	$velden = explode (',', $disprow['Column']);
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$tablerow[$velden[0]].' - '.$tablerow[$velden[1]].'</div></div>';
}

function DisplayVertaald ($tablerow, $disprow) {
	return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal($tablerow[$disprow['Column']]).'</div></div>';
}

function DisplayCapaciteit ($tablerow, $disprow) {
	if ($tablerow[$disprow['Column']]!='') {
		$waarde = SpanningDecimals($tablerow[$disprow['Column']]);
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$waarde.' '.$disprow['Suffix'].'</div></div>';
	} else {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.vertaal('onbekend').'</div></div>';
	}
}

function DisplayIcoonSpanningen ($tablerow, $disprow) {
	$spanningen = explode ('-', $tablerow[$disprow['Column']]);
	if (count($spanningen)>1) {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.$tablerow[$disprow['Column']].' '.$disprow['Suffix'].'</div></div>';
	} else {
		return '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal('Spanning').'</div><div class="balloon-gegwaarde">'.$spanningen[0].' '.$disprow['Suffix'].'</div></div>';
	}
}

function DisplayValues ($type, $tablerow, $disprow) {
	$retval='';
	for ($x = 1; $x < count($disprow)-1; $x++) {
		if ($disprow[$x]['Type']=='Small') {
			$retval .= '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="balloon-gegwaarde">'.utf8_encode($tablerow[$disprow[$x]['Column']]).' '.$disprow[$x]['Suffix'].'</div></div>';
		} elseif ($disprow[$x]['Type']=='Big') {
			$retval .= '<div class="balloon-gegevensbig"><div class="balloon-bigtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="balloon-bigwaarde">'.$tablerow[$disprow[$x]['Column']].'</div></div>';
		} elseif ($disprow[$x]['Type']=='Func') {
			$func = $disprow[$x]['FuncName'];
			$retval .= $func($tablerow, $disprow[$x]);
		} elseif ($disprow[$x]['Type']=='Link') {
			$retval .= '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="balloon-gegwaarde"><a href="edititem.php?ID='.$_GET['ID'].'">'.$_GET['ID'].'</a></div></div>';
		} elseif ($disprow[$x]['Type']=='Blanco') {
			$retval .= '<div class="balloon-gegevensrij"><div class="balloon-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="balloon-gegwaarde"></div></div>';
		}
	}
	return $retval;
}

//Mainloop ==========

$center = "ST_GeomFromText('".$rij['center']."')";
$ip = "INET6_ATON('".@$_SERVER['REMOTE_ADDR']. "')";
$query = @$_GET['ID'];
$zoom = 0;
$viewSQL = "INSERT INTO views (ip, center, hoogte, query, accestype) VALUES ($ip, $center,'$zoom','$query', 'WB')";
//	echo $viewSQL . '\n';
$test=mysqli_query($connection, $viewSQL);
if (!$test) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query')); }

if (isset($_GET['Viewer']) AND $_GET['Viewer']=='Browser') {
	echo '<!DOCTYPE html><html>';	
	echo '<head><title>Netkaartballoon</title>';
	echo '  <link rel="stylesheet" type="text/css" href="netkaart.css">';
	echo '</head><body>';
}
$FotoURLOK = isValidImageURL($rij['FotoURL']);
if ($FotoURLOK) {
	$orientation = GetOrientation($rij['FotoURL']);
} else {
	$orientation= 'portrait';
}
echo '<div class="balloon-outline">';
		echo '<div class="balloon-foto-'.$orientation.'">';
			echo DisplayFoto($editTP, $rij);
		echo '</div>'; // div foto-
		echo '<div class="balloon-gegevens">';
			echo '<div class="balloon-header">';
				echo DisplayTitle ($rij, $display[0]);
			echo '</div>';  // div header
			echo DisplayValues($editTP, $rij, $display);
			echo DisplayOpmerkingen($rij, end($display));
		echo '</div>'; // div gegevens
		echo '<div class="balloon-logo">';
			echo '<a id="balloon-feedback" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;" title="'.vertaal('Meld een fout of aanvullende informatie over dit object').'">'.vertaal('Feedback melden').'</a><a target="_blank" href="http://www.hoogspanningsnet.com" title="'.vertaal('Naar de website').'"><img src="'.$IconDir.'embleem_hoogspanningsnet_transpa.png"></a>';
		echo '</div>'; // logo
	echo '</div>'; // div outline
if (isset($_GET['Viewer']) AND $_GET['Viewer']=='Browser') {
	echo '</body>';
}
	
//Close connection to MySQL server
mysqli_close($connection);
?>