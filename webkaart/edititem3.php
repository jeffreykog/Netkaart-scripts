<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script makes editing of objects  */
/* on the netkaart possible. Editable    */
/* fields in the table start with a      */
/* Capital letter                        */
/* ===================================== */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('hulpfuncties.php');
require('phpsqlajax_dbinfo.php');

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}

function CheckChanges($kaartid, $nieuwen, $ouden, $dbase) {
	$chgquery = '';
	$chqstr = '';

	$kaartid = substr($kaartid,1);
	for ($i; $i<=count($nieuwen); $i++ ) {
		$key=key($nieuwen);
		$val=$nieuwen[$key];
		
		if ($val != $ouden[$key] AND $key!='submit' AND $key!='log') {
			$chgquery = $chgquery . '`' . $key . '`="'.$val.'",';
			$chqstr = $chqstr. ' '.$key. ',';
		}
		next($nieuwen);
	}
	if (strlen($chqstr) > 0) {
		$chqstr = '`log`="'. $ouden['log'] . date('d/m/Y H:i:s'). ' Updated fields:'. substr($chqstr, 0, -1) . ' by USER:' . $_COOKIE['editusername'] . ' through edititem.php\n"';
	}
	if (VindFouteWoorden($chgquery) !== FALSE) {
		$chgquery ='';
	}
	if (strlen($chgquery) > 0) {
		$chgquery = 'UPDATE '.$dbase.' SET '. $chgquery . $chqstr. ' WHERE ID="'.$kaartid.'"';
	}
	return $chgquery;
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

function DisplayTitle ($tablerow, $disprow) {
	global $loggedin;
	
	$retval = '<h1>'.vertaal($disprow['DisplayNaam']);
	if ($loggedin) {
		$retval .= '<div class="edititem-button"><a id="balloon-edititemsend" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;" title="Verzend gegevens">'.vertaal('Verzenden').'</a></div>';
	} else {
		$retval .= '<div class="edititem-button"><a id="balloon-login" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;" title="Login">'.vertaal('Inloggen om te bewerken').'</a></div>';
	}
	$retval .= '</h1>';
	return $retval;
}

function DisplayID ($tablerow, $disprow) {
	return '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div><div class="edititem-geginput">'.$_GET['ID'].'</div></div>';
}

function DisplayInput ($tablerow, $disprow) {
	global $loggedin;
	
	if ($loggedin) {
		if ($disprow['Suffix']!=='') {
			return '<div class="edititem-geginputsuff"><input type="text" name="'.$disprow['Column'].'" value="'.$tablerow[$disprow['Column']].'"> '.$disprow['Suffix'].'</div>';
		} else {
			return '<div class="edititem-geginput"><input type="text" name="'.$disprow['Column'].'" value="'.$tablerow[$disprow['Column']].'"></div>';
		}
	} else {
		return '<div class="edititem-geginput">'.$tablerow[$disprow['Column']].' '.$disprow['Suffix'].'</div>';
	}
}

function DisplayDate ($tablerow, $disprow) {
	global $loggedin;

	if ($loggedin) {
		return '<div class="edititem-geginput"><input type="number" name="'.$disprow['Column'].'" min="0" max="9999" value="'.$tablerow[$disprow['Column']].'"> '.$disprow['Suffix'].'</div>';
	} else { 
		return '<div class="edititem-geginput">'.$tablerow[$disprow['Column']].' '.$disprow['Suffix'].'</div>';
	}
}

function DisplayBigInput ($tablerow, $disprow) {
	global $loggedin;

	if ($loggedin) {
		return '<div class="edititem-bigwaarde"><textarea id="'.$disprow['Column'].'textarea" onkeydown="if(event.keyCode == 13) {document.getElementById(\''.$disprow['Column'].'textarea\').value = document.getElementById(\''.$disprow['Column'].'textarea\').value + \'\n\'; return false;};"rows="4" name="'.$disprow['Column'].'">'.$tablerow[$disprow['Column']].'</textarea></div>';
	} else {
		return '<div class="edititem-bigwaarde">'.$tablerow[$disprow['Column']].'</div>';
	}
}

function PulldownValues ($valarr, $selected) {
	$retval = '';
	foreach ($valarr as $value) {
		if ($value == $selected) {
			$retval .= '<option value="'.$value.'" selected>'.vertaal($value).'</option>';
		} elseif (end($valarr)=='anders/onbekend' AND end($valarr)==$value AND $selected=='') {
			$retval .= '<option value="anders/onbekend" selected>'.vertaal('anders/onbekend').'</option>';
		} else {
			$retval .= '<option value="'.$value.'">'.vertaal($value).'</option>';
		}
	}
	return $retval;	
}

function DisplayPulldown ($tablerow, $disprow) {
	global $loggedin;
	
	if ($loggedin) {
		return '<div class="edititem-geginput"><select name="'.$disprow['Column'].'">'.PulldownValues($disprow['AllowedValues'], $tablerow[$disprow['Column']]).'</select></div>';
	} else {
		return '<div class="edititem-geginput">'.$tablerow[$disprow['Column']].'</div>';
	}
}

function DisplayFotoURL ($tablerow, $disprow) {
	$retval ='';
	$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow['DisplayNaam']).'</div>';
	$retval .= DisplayInput($tablerow, $disprow);
	$retval .= '</div>';
	if (isValidImageURL($tablerow[$disprow['Column']])) {
		$retval .= '<img class="edititem-foto" src="'.$tablerow[$disprow['Column']].'">';
	} else {
		$retval .= '<div class="edititem-fotoleeg"></div>';
	}
	return $retval;
}

function DisplayLog($tablerow, $disprow) {
	$retval = '<div class="edititem-gegrij"><div class="edititem-bigtitel">'.vertaal($disprow['DisplayNaam']).'</div>';
	$retval .= '<div class="edititem-bigwaarde"><input type="hidden" name="'.$disprow['Column'].'" value="'.$tablerow[$disprow['Column']].'">';
	if ($tablerow[$disprow['Column']]<>'') {
		$retval .= '<i>'.nl2br($tablerow[$disprow['Column']]).'</i>';
	} else {
		$retval .= '<i>'.vertaal('Geen wijzigingen').'.</i>';	
	}
	$retval .= '</div>';
	return $retval;
}
function DisplayValues ($type, $tablerow, $disprow) {
	global $loggedin;
	
	$retval='';
	if ($loggedin) {
		$retval .= '<form id="editdata" name="editdata" action="">';
	}
	for ($x = 1; $x < count($disprow); $x++) {
		if ($disprow[$x]['Type']=='Small') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div>';
			$retval .= DisplayInput($tablerow, $disprow[$x]).'</div>';
		} elseif ($disprow[$x]['Type']=='Pulldown') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div>';
			$retval .= DisplayPulldown($tablerow, $disprow[$x]).'</div>';
		} elseif ($disprow[$x]['Type']=='Date') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div>';
			$retval .= DisplayDate($tablerow, $disprow[$x]).'</div>';	
		} elseif ($disprow[$x]['Type']=='Big') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-bigtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div>';
			$retval .= DisplayBigInput($tablerow, $disprow[$x]).'</div>';
		} elseif ($disprow[$x]['Type']=='Func') {
			$func = $disprow[$x]['FuncName'];
			$retval .= $func($tablerow, $disprow[$x]);
		} elseif ($disprow[$x]['Type']=='Link') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="edititem-gegwaarde"><a href="edititem.php?ID='.$_GET['ID'].'">'.$_GET['ID'].'</a></div></div>';
		} elseif ($disprow[$x]['Type']=='Blanco') {
			$retval .= '<div class="edititem-gegrij"><div class="edititem-gegtitel">'.vertaal($disprow[$x]['DisplayNaam']).'</div><div class="edititem-gegwaarde"></div></div>';
		}
	}
	if ($loggedin) {
		$retval .= '</form>';
	}
	return $retval;
}

function CheckLand($land, $veld) {
	if (country_code_to_country($land)==$land OR strlen($land)>2){
		return '<div class="foutmelding">'.vertaal('Ongeldige landcode').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	} else {
		return '';
	}
}

function CheckNumber ($nummer, $veld) {
	if ($nummer<>'' AND !is_numeric($nummer) ) {
		return '<div class="foutmelding">'.vertaal('Geen integer').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	} else {
		return '';
	}
}

function CheckSpanningen ($spanningen, $veld) {
	$spanningen = explode('-',$spanningen);
	if (is_array($spanningen)) {
		foreach ($spanningen as $spanning) {
			if (!is_numeric($spanning)) {
				return '<div class="foutmelding">'.vertaal('Geen integer').' '.vertaal('ingevuld bij').' '.vertaal($veld).' ('.$spanning.')<br></div>';
			} else {
				return '';
			}
		}
	} else {
		return '<div class="foutmelding">'.vertaal('Onjuist format').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	}
}

function CheckACDC($acdc, $veld) {
	if ($acdc=='AC' OR $acdc=='DC') {
		return '';
	} else {
		return '<div class="foutmelding">'.vertaal('Geen AC of DC').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	}
}

function CheckDatum($datum, $veld) {
	if (!is_numeric($datum) OR $datum<0 OR $datum>9999) {
		return '<div class="foutmelding">'.vertaal('Geen jaar tussen 0 en 9999').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	} else {
		return '';
	}
}
	
function CheckMastCircuits($circuits, $veld) {
	$foutstr='';
	$delimiters = ' .,;-|\\/';
	$lijnen = rtrim($circuits, $delimiters);
	$delimiters = str_split($delimiters);
	$lijnen = str_replace($delimiters,',',$lijnen);
	$lijnen = str_replace('v','',$lijnen);
	$lijnen = explode(',',$lijnen);
	
	if (is_array($lijnen)) {
		foreach ($lijnen as $lijn) {
			if ($lijn<>'') {
				if (!is_numeric($lijn)) {
					$foutstr.= '<div class="foutmelding">'.vertaal('Geen geldig circuit ID').' '.vertaal('ingevuld bij').' '.vertaal($veld).' ('. $lijn.')<br></div>';
				}
			} 
		}
	} 
	return $foutstr;
}
	
function CheckFotoURL($url, $veld) {
	if ($url<>'' AND !isValidImageURL($url)) {
		return '<div class="foutmelding">'.vertaal('Geen geldige afbeelding URL').' '.vertaal('ingevuld bij').' '.vertaal($veld).'<br></div>';
	} else {
		return '';
	}
}		

function CheckInputValues ($postrow, $disprow) {
	$retval = '';
	for ($i=0; $i < count($postrow); $i++ ) {
		$key=key($postrow);
		foreach ($disprow as $value) {
			if ($value['Column'] == $key) {
				if (array_key_exists('CheckFuncName',$value)) {
					$val=$postrow[$key];
					$checkfunc = $value['CheckFuncName'];
					$retval .= $checkfunc($val, $value['DisplayNaam']);
				}
			}
		}
		next($postrow);
	}
	return $retval;
}

$loggedin = CheckLoggedIn(FALSE);

if (isset($_GET['ID'])) {
	$editID = $_GET['ID'];
	$editTP = $editID[0];
	$editID = substr($editID,1);
}

if ($editTP == 'v') {
	$table = 'Verbindingen';
	$query = 'SELECT *, astext(linestring) as lijn FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $VerbindingenEdit;
} elseif ($editTP == 's') {
	$table = 'Stationsiconen';
	$query = 'SELECT * FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $StationsiconenEdit;
} elseif ($editTP == 'm') {
	$table = 'Masten';
	$query = 'SELECT *, X(point) as lon, Y(point) as lat FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $MastenEdit;
} elseif ($editTP == 't') {
	$table = 'Stations';
	$query = 'SELECT * FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $StationsterreinEdit;
} elseif ($editTP == 'k') {
	$table = 'Knooppunten';
	$query = 'SELECT * FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $KnooppuntenEdit;
} elseif ($editTP == 'n') {
	$table = 'Netopeningen';
	$query = 'SELECT * FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $OpeningenEdit;
} elseif ($editTP == 'b') {
	$table = 'Bedrijfsmiddelen';
	$query = 'SELECT * FROM '.$table.' WHERE ID="'.$editID.'"';
	$editArr = $BedrijfsmiddelenEdit;
} else {
	printerror(vertaal("Geen correct gegevens type").".");
	die();
}

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), 'Not connected to MySQL')); }
mysqli_query($connection, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

// Get data from record.
$gegevens = mysqli_query($connection, $query);
if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query'));}
$rij = mysqli_fetch_assoc($gegevens);
if (!$rij) {
	printerror(vertaal("ID niet gevonden").".");
	die();
}
if ($editTP == 'v') {
	$rij['Lengte'] = number_format(greatCircleLength($rij['lijn'])/1000, 2, ',', '.');
}

echo '<div class="edititem-outline">';
echo 	'<div class="edititem-header">';
echo 		DisplayTitle ($rij, $editArr[0]);
echo 	'</div>';
echo 	'<div class="edititem-gegevens">';

if (isset($_GET['submit']) AND $_GET['submit']=='Invoeren!' AND $loggedin) {
	$foutstr = CheckInputValues($_POST, $editArr);
	if ($foutstr=='') {
		$updquery = CheckChanges($_GET['ID'],$_POST, $rij, $table);
		if ($updquery !='') {
			//		echo '<i><small>'.nl2br($updquery). '</small></i><p>';
			$result = mysqli_query($connection, $updquery);
			if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query'));	}
			$gegevens = mysqli_query($connection, $query);
			if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query'));	}
			$rij = mysqli_fetch_assoc($gegevens);
			$rij['Lengte'] = number_format(greatCircleLength($rij['lijn'])/1000, 2, ',', '.');
			echo '<i>'.vertaal('Item bijgewerkt').'!</i><p>';
			$query = 'INSERT INTO wijzigingen (`dader`, `ip`, `wijze`) VALUES ("' .$_COOKIE['editusername']. '", "' .$_SERVER['REMOTE_ADDR']. '", "Edititem '.$_GET['ID'].' - '.$table. ' - '.$rij['Naam']. '")';
			//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query'));	}
		} else {
			echo '<i>'.vertaal('Item niet bijgewerkt, waarden waren niet gewijzigd').'.</i><p>';
		}
	} else {
		echo $foutstr;
	}
} 

//Mainloop ==========
echo 		DisplayValues($editTP, $rij, $editArr);
echo 	'</div>'; // div gegevens
echo 	'<div class="balloon-logo">';
echo 		'<img src="'.$IconDir.'embleem_hoogspanningsnet_transpa.png">';
echo 	'</div>'; // logo
echo '</div>'; // div outline

//Close connection to MySQL server
mysqli_close($connection);
?>
