<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the styling of  */
/* the netkaart based on stijlen.kml     */
/*                                       */
/* V3: Does not gereate balloons with    */
/* but calls a php-script for this       */
/* ===================================== */

require('../phpsqlajax_dbinfo.php');
require('../hulpfuncties.php');

 error_reporting(E_ALL);
 ini_set('display_errors', 1);

$stijlen = simplexml_load_file('../stijlen.xml');

function geefstijlinfo($spann, $type) {
	global $stijlen;
	
	foreach ($stijlen->Kleuren->Spanning as $kleurstijl):
		if ($spann >= floatval($kleurstijl['LaagGrens']) AND $spann <= floatval($kleurstijl['HoogGrens'])) {
			return $kleurstijl->$type;
		}
	endforeach;
}

//print_r( geefstijlinfo(380,'Verbinding'));

echo '<head><meta http-equiv="Content-type" value="text/html; charset=UTF-8" /></head><body>Opnieuw genereren van '.$StyleFile.' is gestart.<br>';

// Opens a connection to a MySQL server.
$connection = new mysqli($server, $username, $password, $database);
if ($connection->connect_errno) {
    echo "Failed to connect to MySQL";
}

// ============================= Lijn stijlen =================================
echo 'Lijn stijlen : ';
$query = "SELECT Spanning FROM Verbindingen WHERE Hoofdtype='Lijn' GROUP BY Spanning ORDER BY spanning DESC";
$styles = mysqli_query($connection, $query);
if (!$styles){ 
	die('Invalid query: ' . mysqli_error($connection));
}
$count = 0;
while ($row = @mysqli_fetch_assoc($styles)) {
	++$count; 
	$clrinfo = geefstijlinfo($row['Spanning'],'Verbinding')->Lijn;
	$xml[] = '<Style id="'. $row['Spanning'].'L">';
	$xml[] = 	'<LineStyle>';
	$xml[] = 		'<color>' . maakkleur($clrinfo['Color'],$clrinfo['Alpha']).'</color>';
	$xml[] = 		'<width>' . $clrinfo['Width'] . '</width>';
	$xml[] = 	'</LineStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffececec</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ============================ Einde Lijn stijlen ============================

// ============================= Kabel stijlen ================================
echo 'Kabel stijlen : ';
$query = "SELECT Spanning FROM Verbindingen WHERE Hoofdtype='Kabel' GROUP BY Spanning ORDER BY spanning DESC";
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
	}
$count = 0;
while ($row = @mysqli_fetch_assoc($styles)) {
	++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'Verbinding')->Kabel;
	$xml[] = '<Style id="'. $row['Spanning'].'K">';
	$xml[] = 	'<LineStyle>';
	$xml[] = 		'<color>' . maakkleur($clrinfo['Color'],$clrinfo['Alpha']).'</color>';
	$xml[] = 		'<width>' . $clrinfo['Width'] . '</width>';
	$xml[] = 	'</LineStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffececec</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ============================ Einde Kabel stijlen ============================

// ============================== Stationicons stijlen =============================
echo 'Stationsiconen stijlen : ';
$query = 'SELECT Spanning, Spanningen FROM `Stationsiconen` GROUP BY Spanningen';
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
}

// print een style per type icon
$count = 0;
while ($row = @mysqli_fetch_assoc($styles))
{
	$count = ++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'StationsIcoon');
	$xml[] = '<Style id="label' .$row['Spanningen']. 'stijl">';
	$xml[] = 	'<IconStyle>';
	$xml[] = 		'<Icon><href>'.$WebServer.$UseDir.'iconnew.php?spans='. $row['Spanningen']. '</href></Icon>';
	$xml[] = 		'<scale>'.$clrinfo->Icoon['Size'].'</scale>';
	$xml[] = 	'</IconStyle>';
	$xml[] = 	'<LabelStyle>';
	$xml[] = 		'<scale>'. $clrinfo->Label['Size'] .'</scale>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Label['Color'],$clrinfo->Label['Alpha']) . '</color>';
	$xml[] = 	'</LabelStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffececec</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ========================= EINDE Station stijlen ===========================

// =========================== Knooppunt stijlen =============================
echo 'Knooppunt stijlen : ';
$query = 'SELECT Spanning, IconPNG FROM `Knooppunten` GROUP BY Spanning';
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
}

// print een style per type icon
$count = 0;
while ($row = @mysqli_fetch_assoc($styles))
{
	$count = ++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'Neticoon');
	$xml[] = '<Style id="' .$row['IconPNG']. 'knoop">';
	$xml[] = 	'<IconStyle>';
	$xml[] = 		'<Icon><href> <![CDATA['.$WebServer.$UseDir.'iconnew.php?type=knpp&spans='. $row['Spanning'].']]></href></Icon>';
	$xml[] = 		'<scale>'.$clrinfo->Icoon['Size'].'</scale>';
	$xml[] = 	'</IconStyle>';
	$xml[] = 	'<LabelStyle>';
	$xml[] = 		'<scale>'. $clrinfo->Label['Size'] .'</scale>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Label['Color'],$clrinfo->Label['Alpha']) . '</color>';
	$xml[] = 	'</LabelStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffececec</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ========================= EINDE Knooppunt stijlen =========================

// ========================= Netopening stijlen ==============================
echo 'Netopening stijlen : ';
$query = 'SELECT Spanning,IconPNG FROM `Netopeningen` GROUP BY IconPNG';
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
}

// print een style per type icon
$count = 0;
while ($row = @mysqli_fetch_assoc($styles))
{
	$count = ++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'Neticoon');
	$xml[] = '<Style id="' .$row['IconPNG']. 'open">';
	$xml[] = 	'<IconStyle>';
	$xml[] = 		'<Icon><href>'.$WebServer.$UseDir.$IconDir.$row['IconPNG'] . '</href></Icon>';
	$xml[] = 		'<scale>'.$clrinfo->Icoon['Size'].'</scale>';
	$xml[] = 	'</IconStyle>';
	$xml[] = 	'<LabelStyle>';
	$xml[] = 		'<scale>'. $clrinfo->Label['Size'] .'</scale>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Label['Color'],$clrinfo->Label['Alpha']) . '</color>';
	$xml[] = 	'</LabelStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// =========================== EINDE Netopening stijlen ============================

// ========================= Terreinmarkering stijlen =========================
echo 'Terreinmarkeringen stijlen : ';
$query = "SELECT Spanning FROM Stations GROUP BY Spanning";
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
	}
$count = 0;
while ($row = @mysqli_fetch_assoc($styles)) {
	++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'Terreinmarkering');
	$xml[] = '<Style id="'. $row['Spanning'].'SO">';
	$xml[] = 	'<LineStyle>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Rand['Color'],$clrinfo->Rand['Alpha']) . '</color>';
	$xml[] = 		'<width>' . $clrinfo->Rand['Width'] . '</width>';
	$xml[] = 	'</LineStyle>';
	$xml[] = 	'<PolyStyle>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Vulling['Color'],$clrinfo->Vulling['Alpha']) . '</color>';
	$xml[] = 	'</PolyStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffececec</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ==================== Einde terreinmarkeringen stijlen =======================

// ========================== Mastbolletjes stijlen ===========================
echo 'Mastbolletjes stijlen : ';
$query = "SELECT Spanning FROM Masten GROUP BY Spanning";
$styles = mysqli_query($connection, $query);
if (!$styles){
	die('Invalid query: ' . mysqli_error($connection));
	}
$count = 0;
while ($row = @mysqli_fetch_assoc($styles)) {
	++$count;
	$clrinfo = geefstijlinfo($row['Spanning'],'Masticoon');
	$xml[] = '<Style id="MB'. $row['Spanning'].'">';
	$xml[] = 	'<IconStyle>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Icoon['Color'],$clrinfo->Icoon['Alpha']) . '</color>';
	$xml[] = 		'<scale>'.$clrinfo->Icoon['Size'].'</scale>';
	$xml[] = 		'<Icon>';
	$xml[] = 			'<href>'.$clrinfo->Icoon['href'].'</href>';
	$xml[] = 		'</Icon>';
	$xml[] = 	'</IconStyle>';
	$xml[] = 	'<LabelStyle>';
	$xml[] = 		'<scale>'. $clrinfo->Label['Size'] .'</scale>';
	$xml[] = 		'<color>' . maakkleur ($clrinfo->Label['Color'],$clrinfo->Label['Alpha']) . '</color>';
	$xml[] = 	'</LabelStyle>';
	$xml[] = 	'<BalloonStyle>';
	$xml[] = 		'<bgColor>ffe8e8e8</bgColor>';
	$xml[] = 		'<text><![CDATA[';
	$xml[] = 			'<iframe width="640" height="360" frameborder="0" scrolling="no" src="'.$WebServer.$UseDir.'balloon3.php?ID=$[ID]&v=3"></iframe>';
	$xml[] = 		']]></text>';
	$xml[] = 	'</BalloonStyle>';
	$xml[] = '</Style>';
}
echo $count.' Stijlen gegenereerd<br>';
// ======================== EINDE Mastbolletjes stijlen =======================

$xmlOutput = join("\n", $xml);
file_put_contents ('../' . $StyleFile , $xmlOutput);
chmod('../' . $StyleFile, 0777);
echo '<br>SUCCES! Bestand ../'.$StyleFile.' is succesvol opnieuw gegenereerd.<br>';
echo '<p><a href="beheer.php">Click here to return to the admin page</a></body>';
?>