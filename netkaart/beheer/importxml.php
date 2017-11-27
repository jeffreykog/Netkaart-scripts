<?php
$ImportScriptVersion = "V1.0b";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

function drienaartwee($coords) {
	$tweeds = '';
	$drieds = preg_split('/ /',trim($coords));
	foreach ($drieds as $dried) {
		$effe = preg_split('/,/',$dried);
		$tweeds = $tweeds . $effe[0]. ' ' . $effe[1]. ',';
	}
	$tweeds = substr($tweeds,0,-1); 
	return trim($tweeds);
}

function findlijnen($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $errorteller;
	global $warnteller;
	global $connection;
	
	$lijnen = $zoeken->xpath('Placemark[LineString]');
	foreach ($lijnen as $lijn){
		$teller++;
		$dir['Naam'] = utf8_decode($lijn->name);

		$dir['ID'] = substr($lijn[0]->attributes()->id,1);
		$dir['ExtData'] = $lijn->ExtendedData;
		if (strlen( trim( $lijn->LineString->coordinates)) > 10) {
			$dir['linestring'] = 'LINESTRING('.drienaartwee($lijn->LineString->coordinates).')';
			if (strpos($dir['Naam'],$dir['Spanning'])===FALSE AND $dir['Spanning']!='0' ) {
				$warnteller++;
				echo '<br><i>WARNING: Line "'.$dir['Naam'].'" has NO or WRONG voltage (should be "'.$dir['Spanning'].' kV") in its name, Continuing without correction.</i>';
			}
			if ($dir['HoofdType']==='Kabel' AND strpos(strtolower($dir['Naam']),'kabel')===FALSE) {
				$warnteller++;
				echo '<br><i>WARNING: Line "'.$dir['Naam'].'" should have "Kabel" in its name, Continuing without correction.</i>';
			}
			$query = 'SELECT * FROM Verbindingen WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
			$aantal = mysqli_num_rows($result);
//Echo 'Aantal is :'.$aantal.'<br>';
			if ($aantal!=0){
//echo $query.'<br>';
				$indbteller++;
				$query = 'UPDATE Verbindingen SET `HoofdType`="'.$dir['HoofdType'].'",`SubType`="'.$dir['SubType'].'",`Naam`="'.$dir['Naam'].'",`Spanning`="'.$dir['Spanning'].'",`ACDC`="'.$dir['ACDC'].'",`Beheerder`="'.$dir['Beheerder'].'",`Land`="'.$dir['Land'].'",`DeelNet`="'.utf8_decode($dir['DeelNet']).'",`linestring`= LineStringFromText(\''.$dir['linestring'].'\') WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
				$result = mysqli_query($connection, $query);
				if (!$result) {	die('Invalid query');	}
			} else {
				$uitdbteller++;
				$query = 'INSERT INTO Verbindingen (`HoofdType`, `SubType`, `Naam`, `Spanning`, `ACDC`, `Beheerder`, `Land`, `DeelNet`, `linestring`) VALUES ("' .$dir['HoofdType']. '","' .$dir['SubType']. '","' .$dir['Naam']. '","' .$dir['Spanning']. '","' .$dir['ACDC']. '","' .$dir['Beheerder']. '","' .$dir['Land']. '","' .utf8_decode($dir['DeelNet']). '",LineStringFromText(\''.$dir['linestring'].'\'))';
//echo $query.'<br>';
				$result = mysqli_query($connection, $query);
				if (!$result) {	die('Invalid query'); }
			}
		} else {
			$errorteller++;
			echo '<br><br><b>ERROR: Line "'.$dir['Naam'].'" has no coordinates, Ignoring this Line.</b><br>';
		}
	}
}

function findstations($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $connection;
	
	$punten = $zoeken->xpath('Placemark[Point]');
	foreach ($punten as $punt){
		$teller++;
		$dir['Naam'] = utf8_decode($punt->name);
		$dir['ID'] = substr($punt[0]->attributes()->id,1);
		$dir['ExtData'] = $punt->ExtendedData;
		$dir['point'] = 'POINT('.drienaartwee($punt->Point->coordinates).')';
		$query = 'SELECT * FROM Stationsiconen WHERE ID="'.$dir['ID'].'"';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die('Invalid query');	}
		if (mysqli_num_rows($result)!=0){
			$indbteller++;
			$query = 'UPDATE Stationsiconen SET `Naam`="'.$dir['Naam'].'",`Land`="'.$dir['Land'].'",`Spanning`="'.$dir['Spanning'].'",`Spanningen`="'.$dir['SubFolder'].'",`ACDC`="'.$dir['ACDC'].'",`HoofdType`="'.$dir['Type'].'",`SubType`="'.$dir['SubType'].'",`point`= PointFromText(\''.$dir['point'].'\') WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		} else {
			$uitdbteller++;
			$query = 'INSERT INTO Stationsiconen (`Naam`, `Land`, `Spanning`, `Spanningen`, `ACDC`, `HoofdType`, `SubType`, `point`) VALUES ("' .$dir['Naam']. '","' .$dir['Land']. '","' .$dir['Spanning']. '","' .$dir['SubFolder']. '","' .$dir['ACDC']. '","' .$dir['Type']. '","' .$dir['SubType']. '",PointFromText(\''.$dir['point'].'\'))';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query'); }
		}
	}
}

function findnodes($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $connection;
	
	$punten = $zoeken->xpath('Placemark[Point]');
	foreach ($punten as $punt){
		$teller++;
		$dir['Naam'] = utf8_decode($punt->name);
		$dir['ID'] = substr($punt[0]->attributes()->id, 1);
		$dir['ExtData'] = $punt->ExtendedData;
		$dir['point'] = 'POINT('.drienaartwee($punt->Point->coordinates).')';
		$query = 'SELECT * FROM Knooppunten WHERE ID="'.$dir['ID'].'"';
		$result = mysqli_query($connection, $query);
		if (!$result) { die('Invalid query');	}
		if (mysqli_num_rows($result)!=0){
			$indbteller++;
			$query = 'UPDATE Knooppunten SET `Naam`="'.$dir['Naam'].'",`Land`="'.$dir['Land'].'",`Spanning`="'.$dir['Spanning'].'",`ACDC`="'.$dir['ACDC'].'",`HoofdType`="'.$dir['Type'].'",`SubType`="'.$dir['SubType'].'",`IconPNG`="'.$dir['IconPNG'].'",`point`= PointFromText(\''.$dir['point'].'\') WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		} else {
			$uitdbteller++;
			$query = 'INSERT INTO Knooppunten (`Naam`, `Land`, `Spanning`, `ACDC`, `HoofdType`, `SubType`, `IconPNG`, `point`) VALUES ("' .$dir['Naam']. '","' .$dir['Land']. '","' .$dir['Spanning']. '","' .$dir['ACDC']. '","' .$dir['Type']. '","' .$dir['SubType']. '","' .$dir['IconPNG']. '",PointFromText(\''.$dir['point'].'\'))';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query'); }
		}
	}
}

function findterrain($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $connection;
	
	$terreinen = $zoeken->xpath('Placemark[Polygon]');
	foreach ($terreinen as $terrein){
		$teller++;
		$dir['Naam'] = utf8_decode($terrein->name);
//echo $dir['Spanning'].'-'.$dir['Naam'].'<br>';
		$dir['ID'] = substr($terrein[0]->attributes()->id,1);
		$dir['ExtData'] = $terrein->ExtendedData;
		$dir['polygon'] = 'POLYGON(('.drienaartwee($terrein->Polygon->outerBoundaryIs->LinearRing->coordinates).'))';
		$query = 'SELECT * FROM Stations WHERE ID="'.$dir['ID'].'"';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die('Invalid query'); }
		if (mysqli_num_rows($result)!=0){
			$indbteller++;
			$query = 'UPDATE Stations SET `HoofdType`="'.$dir['HoofdType'].'",`Naam`="'.$dir['Naam'].'",`Spanning`="'.$dir['Spanning'].'",`ACDC`="'.$dir['ACDC'].'",`Land`="'.$dir['Land'].'",`polygon`= PolyFromText(\''.$dir['polygon'].'\') WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		} else {
			$uitdbteller++;
			$query = 'INSERT INTO Stations (`HoofdType`, `Naam`, `Spanning`, `ACDC`, `Land`, `polygon`) VALUES ("' .$dir['HoofdType']. '","' .$dir['Naam']. '","' .$dir['Spanning']. '","' .$dir['ACDC']. '","' .$dir['Land']. '",PolyFromText(\''.$dir['polygon'].'\'))';
// echo $query.'<br>';
		$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		}
	}
}

function findopening($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $connection;
	
	$openingen = $zoeken->xpath('Placemark[Point]');
	foreach ($openingen as $opening){
		$teller++;
		$dir['Naam'] = utf8_decode($opening->name);
		$dir['ID'] = substr($opening[0]->attributes()->id,1);
		$dir['ExtData'] = $opening->ExtendedData;
		$dir['point'] = 'POINT('.drienaartwee($opening->Point->coordinates).')';
		$dir['IconPNG'] = 'netopening.png';
		$query = 'SELECT * FROM Netopeningen WHERE ID="'.$dir['ID'].'"';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die('Invalid query'); }
		if (mysqli_num_rows($result)!=0){
			$indbteller++;
			$query = 'UPDATE Netopeningen SET `Naam`="'.$dir['Naam'].'",`Land`="'.$dir['Land'].'",`HoofdType`="'.$dir['Type'].'",`IconPNG`="'.$dir['IconPNG'].'",`point`= PointFromText(\''.$dir['point'].'\') WHERE ID="'.$dir['ID'].'"';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query'); }
		} else {
			$uitdbteller++;
			$query = 'INSERT INTO Netopeningen (`Naam`, `Land`, `HoofdType`, `IconPNG`, `point`) VALUES ("' .$dir['Naam']. '","' .$dir['Land']. '","' .$dir['Type']. '","' .$dir['IconPNG']. '",PointFromText(\''.$dir['point'].'\'))';
//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		}
	}
}

function findmast($zoeken) {
	global $dir;
	global $teller;
	global $indbteller;
	global $uitdbteller;
	global $connection;
	
	$masten = $zoeken->xpath('Placemark[Point]');
	foreach ($masten as $mast){
		$teller++;
// Echo "effe is :".$effe."<br>";
		if (strlen($mast->ExtendedData->Data[0]->value)>=2) {
			$dir['Naam'] = utf8_decode($mast->ExtendedData->Data[1]->value);
		}
		else {
			$dir['Naam'] = utf8_decode($mast->name);
		}
		$dir['ID'] = substr($mast->ExtendedData->Data[0]->value,1);
		$dir['ExtData'] = $mast->ExtendedData;
		$dir['point'] = 'POINT('.drienaartwee($mast->Point->coordinates).')';
		$query = 'SELECT * FROM Masten WHERE ID="'.$dir['ID'].'"';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die('Invalid query'); }
		if (@mysqli_num_rows($result)!=0){
			$indbteller++;
			//ECHO 'id = '. $dir['ID'].'<br>';
			//echo print_r(@mysqli_fetch_assoc($result)).'<p>';
			$query = 'UPDATE Masten SET `Naam`="'.$dir['Naam'].'",`Land`="'.$dir['Land'].'",`Spanning`="'.$dir['Spanning'].'", `Verbinding`="'.utf8_decode($dir['Verbinding']).'",`point`= PointFromText(\''.$dir['point'].'\') WHERE ID="'.$dir['ID'].'"';
			//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query'); }
		} else {
			$uitdbteller++;
			$query = 'INSERT INTO Masten (`Naam`, `Land`, `Spanning`, `ACDC`, `Verbinding`, `point`) VALUES ("' .$dir['Naam']. '","' .$dir['Land']. '","' .$dir['Spanning']. '", \'AC\', "' .utf8_decode($dir['Verbinding']). '",PointFromText(\''.$dir['point'].'\'))';
			//echo $query.'<br>';
			$result = mysqli_query($connection, $query);
			if (!$result) {	die('Invalid query');	}
		}
	}
}

function findtypes($zoeken) {
	global $dir;
	
	$types = $zoeken->xpath('Folder[name[contains(.,"Zeekabels")]]');
	foreach ($types as $type) {
		$dir['HoofdType']='Kabel';
		$dir['SubType']='Zee';
		findlijnen($type);
	}
	$types = $zoeken->xpath('Folder[name[contains(.,"Grondkabels")]]');
	foreach ($types as $type) {
		$dir['HoofdType']='Kabel';
		$dir['SubType']='Grond';
		findlijnen($type);
	}
	$types = $zoeken->xpath('Folder[name[contains(.,"Luchtlijnen")]]');
	foreach ($types as $type) {
		$dir['HoofdType']='Lijn';
		$dir['SubType']='';
		findlijnen($type);
	}
}

$time_start = microtime(true); 
echo '<head><meta http-equiv="Content-type" value="text/html; charset=UTF-8" /></head><body>'; 

$infile = 'upload/doc.kml';
if (file_exists ( $infile )) {
	if (file_exists (  $infile.'.tmp' )) {
		echo 'Deleting temporary file : '. $infile.'.tmp' .'<br>';
		unlink ( $infile.'.tmp');
	}
	echo 'Correcting file : '.$infile.'<br>'; 
	$kml = file($infile, FILE_IGNORE_NEW_LINES+FILE_SKIP_EMPTY_LINES);
	$kml[1] = '<kml ns="http://www.opengis.net/kml/2.2">';
	$kml = str_replace('gx:','',$kml);
	$kmlOutput = join("\n", $kml);
	echo 'Writing temporary file : '. $infile.'.tmp' .'<br>';
	file_put_contents ($infile.'.tmp' , $kmlOutput);
	chmod($infile.'.tmp', 0777);
	echo 'Reading file ' . $infile.'.tmp' . ' .... ';
	$kml = simplexml_load_file ( $infile.'.tmp');
	echo 'SUCCES!<br>';
} else {
	exit ( 'Failed to open (file does not exist?) : ' . $infile );
	die ();
}

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) { die('Not connected to MySQL'); }

$errorteller =0;
$warnteller = 0;

$landen = $kml->xpath('//Folder[name[contains(.,"Netkaart ")]]');
foreach ($landen as $land){
	$dir = array('Land' => '', 'Spanning' => '', 'ACDC' => '', 'Beheerder' => '', 'DeelNet' => '', 'HoofdType' => '', 'SubType' => '', 'linestring' => '', 'ID' => '', 'Naam' => '');
	$effe = preg_split('/ /',$land->name);
	$dir['Land']=trim($effe[1]) ;
	echo '<br>Found country : '.$dir['Land'].'<br>';
	echo 'Extracting High Voltage Lines and Cables ....';
	$teller = 0;
	$indbteller = 0;
	$uitdbteller = 0;
	$spanningen = $land->xpath('Folder[name[contains(.,"Hoogspanningsverbindingen")]]/Folder');
	foreach ($spanningen as $spanning) {
		if (strpos($spanning->name,'panningsloos')!== false) {
			$dir['Spanning']=0;
			$dir['ACDC']='';
		} else {
			$effe = preg_split('/[ _]/',$spanning->name);
			$dir['Spanning']=trim($effe[0]);
			if (strpos($spanning->name,'DC')===FALSE) {
				$dir['ACDC']='AC';	
			} else {
				$dir['ACDC']='DC';
			}
		}
		$concessies = $spanning->xpath('Folder[name[contains(.,"Concessie")]]');
		if ($concessies) {
			foreach ($concessies as $concessie) {
				$dir['DeelNet']='';
				$effe = preg_split('/ /',$concessie->name);
				$dir['Beheerder']=trim($effe[1]);
				$deelnetten = $concessie->xpath('Folder[name[contains(.,"Deelnet")]]');
				if ($concessies) {
					foreach ($deelnetten as $deelnet) {
						$dir['DeelNet'] = substr($deelnet->name, strpos($deelnet->name, "Deelnet ") + 8);
//						$effe = preg_split('/ /',$deelnet->name,2);
//						$dir['DeelNet']=trim($effe[1]);
						findtypes($deelnet);
					}
				} else {$dir['DeelNet']='';}
				findtypes($concessie);
			}
		} else {$dir['Beheerder']=''; $dir['DeelNet']='';}
		findtypes($spanning);
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';

	echo 'Extracting Station Icons ....';
	$dir = array('ID' => '', 'Naam' => '', 'Spanning' => '', 'Land' => $dir['Land'], 'ACDC' => '', 'Type' => 'Station', 'SubType' => '', 'IconPNG' => '', 'point' => '', 'SubFolder' => '');
	$teller = 0; 
	$indbteller = 0;
	$uitdbteller = 0;
	$spanningen = $land->xpath('Folder[name[contains(.,"Stationsiconen")]]/Folder');
	foreach ($spanningen as $spanning) {
		if (strpos($spanning->name,'panningsloos')!== false) {
			$dir['Spanning']=0;
			$dir['ACDC']='';
			$dir['IconPNG'] = 'not-used.png';
			$subfolders = $spanning->xpath('Folder');
			foreach ($subfolders as $subfolder) {
				$effe = preg_split('/ /',$subfolder->name);
				$dir['SubFolder']=trim($effe[0]);
				findstations($subfolder);
			}
		} else {
			$effe = preg_split('/[ ]/',$spanning->name);
			$dir['Spanning']=trim($effe[0]);
			if (strpos($spanning->name,'DC')===FALSE) {
				$dir['ACDC']='AC';	
			} else {
				$dir['ACDC']='DC';
			}
			$dir['SubFolder']=$dir['Spanning'];
			$dir['IconPNG'] = preg_replace("([^\w\s\d\-_~,;:\[\]\*\].]|[\.]{2,})", '', $dir['SubFolder']) . '.png';
			findstations($spanning);
			
			$subfolders = $spanning->xpath('Folder');
			foreach ($subfolders as $subfolder) {
				$dir['SubFolder']='';
				$effe = preg_split('/ /',$subfolder->name);
				$dir['SubFolder']=trim($effe[0]);
				$dir['IconPNG'] = preg_replace("([^\w\s\d\-_~,;:\[\]\*\].]|[\.]{2,})", '', $dir['SubFolder']) . '.png';
				findstations($subfolder);
			}
		}
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';
		
	echo 'Extracting Node Icons ....';
	$dir = array('ID' => '', 'Naam' => '', 'Spanning' => '', 'Land' => $dir['Land'], 'ACDC' => '', 'Type' => 'Knoop', 'SubType' => '', 'IconPNG' => '', 'point' => '', 'SubFolder' => '');
	$teller = 0;
	$indbteller = 0;
	$uitdbteller = 0;
	$spanningen = $land->xpath('Folder[name[contains(.,"Knooppunten")]]/Folder');
	foreach ($spanningen as $spanning) {
		if (strpos($spanning->name,'panningsloos')!== false) {
			$dir['Spanning']=0;
			$dir['ACDC']='';
			$dir['SubFolder']='';
			$dir['IconPNG'] = 'node-0.png';
			findnodes($spanning);
		} else {
			$effe = preg_split('/[ ]/',$spanning->name);
			$dir['Spanning']=trim($effe[0]);
			if (strpos($spanning->name,'DC')===FALSE) {
				$dir['ACDC']='AC';	
			} else {
				$dir['ACDC']='DC';
			}
			$dir['IconPNG'] = 'node-' . $dir['Spanning'] . '.png';
			findnodes($spanning);
		}
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';
		
	echo 'Extracting Terrain markings ....';
	$dir = array('ID' => '', 'Naam' => '', 'Spanning' => '', 'Land' => $dir['Land'], 'ACDC' => '', 'HoofdType' => 'Station', 'polygon' => '');
	$teller = 0;
	$indbteller = 0;
	$uitdbteller = 0;
	$spanningen = $land->xpath('Folder[name[contains(.,"Terreinmarkeringen")]]/Folder');
	foreach ($spanningen as $spanning) {
		if (strpos($spanning->name,'panningsloos')!== false) {
			$dir['Spanning']=0;
			$dir['ACDC']='';
			findterrain($spanning);
		} else {
			$effe = preg_split('/[ ]/',$spanning->name);
			$dir['Spanning']=trim($effe[0]);
			if (strpos($spanning->name,'DC')===FALSE) {
				$dir['ACDC']='AC';	
			} else {
				$dir['ACDC']='DC';
			}
			findterrain($spanning);
		}
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';
	
	echo 'Extracting Grid Openings ....';
	$dir = array('ID' => '', 'Naam' => '', 'Land' => $dir['Land'], 'Spanning' => '', 'ACDC' => '', 'Type' => 'Opening', 'IconPNG' => '', 'point' => '');
	$teller = 0;
	$indbteller = 0;
	$uitdbteller = 0;
	$openingen = $land->xpath('Folder[name[contains(.,"Netopeningen")]]');
	foreach ($openingen as $opening) {
		findopening($opening);
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';

	echo 'Extracting Highvoltage Towers ....';
	$dir = array('ID' => '', 'Naam' => '', 'Spanning' => '', 'Verbinding' => '', 'Land' => $dir['Land'], 'SubFolder' => '');
	$teller = 0; 
	$indbteller = 0;
	$uitdbteller = 0;
	$spanningen = $land->xpath('Folder[name[contains(.,"Masten")]]/Folder');
	foreach ($spanningen as $spanning) {
		if (strpos($spanning->name,'panningsloos')!== false) {
			$dir['Spanning']=0;
			$dir['SubFolder']=$dir['Spanning'];
			findmast($spanning);

			$verbindingen = $spanning->xpath('Folder');
			foreach ($verbindingen as $verbinding) {
				$dir['Verbinding']=trim($verbinding->name);
				findmast($verbinding);
			}
		} else {
			$effe = preg_split('/[ ]/',$spanning->name);
			$dir['Spanning']=trim($effe[0]);
			$dir['SubFolder']=$dir['Spanning'];
			findmast($spanning);
			
			$verbindingen = $spanning->xpath('Folder');
			foreach ($verbindingen as $verbinding) {
				$dir['Verbinding']=trim($verbinding->name);
				findmast($verbinding);
			}
		}
	}
	echo '<br>&nbsp;&nbsp;&nbsp;found '.$teller.' entries, '.$indbteller.' were in the database, '. $uitdbteller .' were new.<br>';
}

$query = 'INSERT INTO wijzigingen (`dader`, `ip`, `wijze`) VALUES ("' .$_COOKIE['editusername']. '", "' .$_SERVER['REMOTE_ADDR']. '","Importscript ' .$ImportScriptVersion. '  - file: '.$_FILES['file']['name'].'")';
//echo $query.'<br>';
$result = mysqli_query($connection, $query);
if (!$result) {	die('Invalid query'); }
//close the connection
mysqli_close($connection);

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<br>Import finished, there were <b>'.$errorteller.' error(s)</b> and <i>'.$warnteller.' warning(s)</i>.<br>';
echo '<br><b>Total Execution Time:</b> '.$execution_time.' secs';
echo '<p><a href="beheer.php">Click here to return to the admin page</a></body>';
?>