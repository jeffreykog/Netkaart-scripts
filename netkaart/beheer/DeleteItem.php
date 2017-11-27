<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script deletes objects on the    */
/* netkaart			                     */
/* ===================================== */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('../hulpfuncties.php');
require('../phpsqlajax_dbinfo.php');

$returnstr= "<p><a href='beheer.php'> Click here to return to the admin page.</a>";

$loggedin = CheckLoggedIn(TRUE);

header('Content-Type: text/html; charset=LATIN-1');
echo "<html><head></head><body>";

if (!isset($_GET["ID"])) {
	Exit ("Geen ID meegegeven.".$returnstr."</body>");
}

$editID = $_GET["ID"];
$editTP = $editID[0];
$editID = substr($editID,1);

if (!is_numeric($editID)) {
	Exit ("ID ".$editID." is geen integer.".$returnstr."</body>");
}

if ($editTP == 'v') {
	$tabel = "Verbindingen";
	$naam = "Verbinding";
} 
elseif ($editTP == 's') {
	$tabel= "Stationsiconen";
	$naam = "Stationsicoon";
} 
elseif ($editTP == 'm') {
	$tabel= "Masten";
	$naam = "Mast";
} 
elseif ($editTP == 't') {
	$tabel= "Stations";
	$naam = "Stationsmarkering";
} 
elseif ($editTP == 'k') {
	$tabel= "Knooppunten";
	$naam = "Knooppunt";
}
else {
	Exit ("Onbekende ID letter".$returnstr."</body></html>");
}

// Opens a connection to a MySQL server.
$connection = new mysqli($server, $username, $password, $database);
if ($connection->connect_errno) {exit ("Failed to connect to MySQL");};
//execute query
$query = "SELECT ID, Naam, Spanning FROM ".$tabel." WHERE ID='".$editID."'";
$gegevens = mysqli_query($connection, $query);
//close the connection
mysqli_close($connection);
if (!$gegevens) {exit("Invalid query</body></html>");}
$count=mysqli_num_rows($gegevens);
if(!$count==1){
	exit("There is no ID ".$_GET['ID']." in the database.".$returnstr."</body></html>");
}

$rij = mysqli_fetch_assoc($gegevens);

if (!isset($_POST['submit'])) {
	echo "<form name='okform' method='post' action='DeleteItem.php?ID=".$_GET['ID']."'>";
	echo "Are you sure you want to delete this ID: <P>";
	echo "<table><tr><td>Type</td><td>".$naam."</td></tr>";
	echo "<tr><td>ID</td><td>".$_GET['ID']."</td></tr>";
	echo "<tr><td>Voltage</td><td>".$rij['Spanning']."</td></tr>";
	echo "<tr><td>Name</td><td>".$rij['Naam']."</td></tr>";
	echo "<tr><td><input type='submit' name='submit' value='YES I want to'></td><td><input type='submit' name='submit' value='NO I have doubts'></td></tr><table>";
	echo "</form></body></html>";
}
elseif ($_POST['submit']=='YES I want to' AND $loggedin) {
	echo "OK, You really wanted it, ID ".$_GET['ID']." is being deleted...";
	// Opens a connection to a MySQL server.
	$connection = new mysqli($server, $username, $password, $database);
	if ($connection->connect_errno) {exit ("Failed to connect to MySQL");};
	
	//execute query
	$query = "DELETE FROM ".$tabel." WHERE ID='".$editID."'";
	$result = mysqli_query($connection, $query);
	if (!$result) {exit('Invalid query</body></html>');	}
	$query = 'INSERT INTO wijzigingen (`dader`, `wijze`) VALUES ("' .$_COOKIE['editusername']. '","DeleteItem ' .$_GET['ID'].' - '.$tabel.' '.$rij['Naam'].'")';
//	echo $query.'<br>';
	$result = mysqli_query($connection, $query);
	//close the connection
	mysqli_close($connection);
	
	if (!$result) {exit('Invalid query</body></html>'); }
	Echo "Done".$returnstr."</body></html>";
} else {
	echo 'Ah, you had doubts! '.$returnstr.'</body>';
}



?>