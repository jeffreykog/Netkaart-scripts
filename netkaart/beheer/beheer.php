<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script show administrator info,  */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../phpsqlajax_dbinfo.php');
require('../hulpfuncties.php');

//Check if were logged in, if not goto the start page
if (CheckLoggedIn(TRUE)==false) {header("location:index.php");}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<table width=100% height=100% border=1><tr>
	<td width=50%><font size=4><b>Welkom op de beheerderspagina van de Netkaart</b></font></td>
	<td width=50%>De laatste 200 wijzigingen:</td>
</tr><tr>
	<td valign=top>
	<form><fieldset>
		<legend>Download volledige Netkaart</legend><p>
		<a href="../genereerkaart.php?BBOX=-180,-90,180,90;CAMERA=10000;EXPERT=true">Download de volledige netkaart hier</a><p>
	</fieldset></form>

	<form name="delete" id="delete" action="DeleteItem.php" method="get"><fieldset>
		<legend>Verwijder object uit de database</legend>
		<table>
			<tr><td>ID van te verwijderen Object :</td><td><input type="text" name="ID"></td></tr>
			<tr><td></td><td><input type="submit" value="Object verwijderen"></td></tr>
		</table></fieldset>
	</form>

	<form><fieldset>
		<legend>Stijlen bestand</legend><p>
		<a href="stijlen.php">Regenereer het stijlen bestand. (na toevoegen nieuwe spanning, stationsicoon, wijzigen balloon etc.)</a><p>
		</fieldset>
	</form>

	<form name="upload" id="upload" action="uploadfile.php" method="post" enctype="multipart/form-data" accept-charset='UTF-8'><fieldset>
		<legend>Importeer KML file</legend>
		<table><tr>
			<td><label for="file">KML File:</label></td>
			<td><input type="file" name="file" id="file"></td></tr>
			<tr><td><input type='submit' name='KMLUploaden' value='KML Importeren' /></td></tr>
		</table>
		</fieldset>
	</form>
	
	<form><fieldset>
		<legend>Kaartgebruik</legend><p>
		<table><tr><td valign="top">
		<table border=1>
			<tr>
				<td><b>Datum</b></td>
				<td><b>Aant.</b></td>
				<td><b>GE</b></td>
				<td><b>Web</b></td>
			</tr>
<?php 
	// Opens a connection to a MySQL server.
	$connection = new mysqli($server, $username, $password, $database);
	if ($connection->connect_errno) {exit ("Failed to connect to MySQL");}
	
	$query = 'SELECT DATE_FORMAT(DATE(tm),"%d/%m/%Y - %a") as Datum,'. 
				'COUNT(tm) as Aantal, '.
				'SUM( IF (method LIKE "G%",1, 0)) as GE,'. 
				'SUM( IF (method LIKE "W%",1, 0)) as Web '.
				'FROM `track` GROUP BY DATE(tm) ORDER BY tm DESC LIMIT 22';
	$wijzigingen = mysqli_query($connection, $query);
	if (!$wijzigingen) {exit("Invalid query</body></html>");}
	
	$hoogste = 0;
	$laagste = 0;
	while ($rij = @mysqli_fetch_assoc($wijzigingen)) {
		echo "<tr><td nowrap>".$rij['Datum']."</td><td align=right>".$rij['Aantal']."</td><td align=right>".$rij['GE']."</td><td align=right>".$rij['Web']."</td></tr>";
		
	}
?>
		</table></td>
		<td width=20></td><td valign="top"><table border=1>
			<tr><td colspan=2><b>Herkomst</b></td><td><b>Aantal</b></td><td><b>Perc</b></td></tr>
<?php
	$query = 'SELECT cntry as Land, count( cntry ) as Aantal, TRUNCATE((Count(cntry)* 100 / (Select Count(*) From `track` WHERE cntry<>"")),1) as Perc FROM `track` WHERE cntry<>"" GROUP BY cntry ORDER BY count( cntry ) DESC';
	$wijzigingen = mysqli_query($connection, $query);
	
	if (!$wijzigingen) {exit("Invalid query</body></html>");}
	while ($rij = @mysqli_fetch_assoc($wijzigingen)) {
		echo "<tr><td>".$rij['Land']."</td><td>".country_code_to_country($rij['Land'])."</td><td align=right>".$rij['Aantal']."</td><td align=right>".$rij['Perc']."%</td></tr>";
	}
?>
			
		</table></td>
		</tr></table>
	</fieldset>
	</form>
	
	</td>
	
	<td>

<?php
	$query = 'SELECT * FROM wijzigingen ORDER by tijd DESC LIMIT 200';
	$wijzigingen = mysqli_query($connection, $query);
	if (!$wijzigingen) {exit("Invalid query</body></html>");}

	//close the connection
	mysqli_close($connection);
	
	echo "<table width=100% height=100%><small>";
	echo "<tr><td><b>Tijd</b></td><td><b>Beheerder</b></td><td><b>Wijziging</b></td></tr>";
	while ($rij = @mysqli_fetch_assoc($wijzigingen)) {
		echo "<tr><td valign=top nowrap>".$rij['tijd']."</td><td valign=top nowrap>".$rij['dader']."</td><td valign=top>".utf8_encode($rij['wijze'])."</td></tr>";
	}
	echo "</small></table>";
	
?>
	</td>
</tr>
</table>
</body>
</html> 