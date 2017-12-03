<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script makes editing of objects  */
/* on the netkaart possible. Editable    */
/* fields in the table start with a      */
/* Capital letter                        */
/*                                       */
/* ===================================== */

require('hulpfuncties.php');
require('phpsqlajax_dbinfo.php');

function GeefDataWeer ($rijtje, $loggedin) {
	$i = 0;
	for ($i; $i <= count($rijtje); $i++ ) {
		$key=key($rijtje);
		if (ctype_upper($key{0})) {
			$val=$rijtje[$key];
			echo '<tr><td>'.htmlentities($key) .'</td>';
			if ($loggedin) { 
				if (htmlentities($key)<>'ID') {
					echo '<td><input type="text" size="60" name="' .$key .'" value="'.$val.'"></td></tr>';
				} else {
					echo '<td>'.$val .'</td></tr>';
				}	
			} else {
				echo '<td>'.$val .'</td></tr>';
			}
			next($rijtje);
		}
	}
}


function CheckChanges($kaartid, $nieuwen, $ouden, $dbase) {
	$chgquery = '';
	$chqstr = '';
		
	$kaartid = substr($kaartid,1);
	for ($i; $i <= count($nieuwen); $i++ ) {
		$key=key($nieuwen);
		$val=$nieuwen[$key];
		if ($val != $ouden[$key] AND $key!='submit') {
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

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);

if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__),'Not connected : ' . mysqli_error($connection))); }

$loggedin = CheckLoggedIn(FALSE);
// $loggedin = false;

$editID = $_GET['ID'];
$editTP = $editID[0];
$editID = substr($editID,1);
header('Content-Type: text/html; charset=LATIN-1');
echo '<html><head><meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1"></head><body>';
echo '<font face="arial"><div style="width:620px; height:320px; overflow:auto;"><table>';

if ($editTP == 'v') {
	echo '<tr><td valign=middle><b>Hoogspanningsverbinding</b></td><td><img width=200 src="http://www.hoogspanningsforum.com/styles/HoogspanHuisstijl%20v3.0/imageset/forum_emblem_small.png"></td></tr>';
	$query = 'SELECT *, astext(linestring) as lijn FROM Verbindingen WHERE ID="'.$editID.'"';
	$table = 'Verbindingen';
} elseif ($editTP == 's') {
	echo '<tr><td valign=middle><b>Transformatorstation</b></td><td><img width=200 src="http://www.hoogspanningsforum.com/styles/HoogspanHuisstijl%20v3.0/imageset/forum_emblem_small.png"></td></tr>';
	$query = 'SELECT * FROM Stationsiconen WHERE ID="'.$editID.'"';
	$table = 'Stationsiconen';
} elseif ($editTP == 'm') {
	echo '<tr><td valign=middle><b>Hoogspanningsmast</b></td><td><img width=200 src="http://www.hoogspanningsforum.com/styles/HoogspanHuisstijl%20v3.0/imageset/forum_emblem_small.png"></td></tr>';
	$query = 'SELECT * FROM Masten WHERE ID="'.$editID.'"';
	$table = 'Masten';
} elseif ($editTP == 't') {
	echo '<tr><td valign=middle><b>Hoogspanningsstation</b></td><td><img width=200 src="http://www.hoogspanningsforum.com/styles/HoogspanHuisstijl%20v3.0/imageset/forum_emblem_small.png"></td></tr>';
	$query = 'SELECT * FROM Stations WHERE ID="'.$editID.'"';
	$table = 'Stations';
} elseif ($editTP == 'k') {
	echo '<tr><td valign=middle><b>Knooppunt</b></td><td><img width=200 src="http://www.hoogspanningsforum.com/styles/HoogspanHuisstijl%20v3.0/imageset/forum_emblem_small.png"></td></tr>';
	$query = 'SELECT * FROM Knooppunten WHERE ID="'.$editID.'"';
	$table = 'Knooppunten';
}

$gegevens = mysqli_query($connection, $query);
if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query: ' . mysqli_error($connection))); }
mysqli_query($connection, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

$rij = mysqli_fetch_assoc($gegevens);
if ($editTP == 'v') {
	$rij['Lengte'] = number_format(greatCircleLength($rij['lijn'])/1000, 2, ',', '.');
}

if ($_POST['submit']=='Invoeren!' AND $loggedin) {
	echo "<p>";
	$updquery = CheckChanges($_GET['ID'], $_POST, $rij, $table);
	if ($updquery !='') { 
		echo '<i><small>'.nl2br($updquery). '</small></i><p>'; 
		$result = mysqli_query($connection, $updquery); 
		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query: ' . mysqli_error($connection)));	}
		$gegevens = mysqli_query($connection, $query);
		if (!$gegevens) { die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query: ' . mysqli_error($connection))); }
		$rij = mysqli_fetch_assoc($gegevens);
		$rij['Lengte'] = number_format(greatCircleLength($rij['lijn'])/1000, 2, ',', '.');	
		echo 'Record updated!<br>';	
		
		$query = 'INSERT INTO wijzigingen (`dader`, `ip`, `wijze`) VALUES ("' .$_COOKIE['editusername']. '", "' .$_SERVER['REMOTE_ADDR']. '", "Edititem '.$_GET['ID'].' - '.$table. ' - '.$rij['Naam']. '")';
		//echo $query.'<br>';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__), 'Invalid query: ' . mysqli_error($connection))); }
	} else {
		echo 'Record not updated, values were unchanged.';
	}
}
if ($loggedin) {
	echo '<form accept-charset="ISO-8859-1" name="editdata" method="post" action="edititem.php?ID='.$_GET['ID'].'&Submitted=True">';
}
 
GeefDataWeer($rij, $loggedin);

echo '</table></div>';
if ($loggedin) {
	echo '<input type="submit" name="submit" value="Invoeren!">';
	echo '</form>';
} 
else {
	echo '<p><a href="loginedit.php?ID='.$_GET['ID'].'">Login to edit<a></font>';
}
echo '</body>';

?>