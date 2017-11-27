<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates KML output of   */
/* Masten on the netkaart                */
/* ===================================== */

function TekenMasten($land) {
	global $kml;
	global $expert;
	global $range; 
	global $sqlbox;
	global $StyleFile;
	global $MastViewHeight;
	global $connection;
	
	$kml[] = '<Folder>';
	$kml[] = '  <name>Masten</name>';
	if ($expert == 'true') {
		$kml[] = '  <open>0</open>';
		$kml[] = '  <visibility>0</visibility>';
	}
	
// ============================== Mastbolletjes  ==============================
  	if ($range<$MastViewHeight) {
	 	$query = 'SELECT *, X( point ) AS lon, Y( point ) AS lat FROM Masten WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND MBRContains(GeomFromText("'.$sqlbox.'"), Masten.point) ORDER BY Spanning DESC, Verbinding ASC, Naam ASC';
 		$result = mysqli_query($connection, $query);
 		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));	}
		// Teken de knooppunten
		// Iterates through the rows, printing a node for each row.
		if (mysqli_num_rows($result)!=0){
			$ouderij = array('Spanning' => -1, 'Verbinding' => '' );
			$huidiglevel = 0;
			while ($rij = @mysqli_fetch_assoc($result)) {
				$BovenGesprongen = FALSE;
				if ($rij['Spanning']<>$ouderij['Spanning'])	{
					$huidiglevel = naarlevel(0,$huidiglevel);
					$kml[] = '<Folder>';
					if (intval($rij['Spanning'])=='0'){
						$kml[] = '<name>Spanningsloos</name>';
					} else {
						$kml[] = '<name>'. SpanningDecimals($rij['Spanning']) . ' kV '. htmlspecialchars($rij['ACDC'], ENT_QUOTES,"ISO-8859-1", true) .'</name>';
					}
					$huidiglevel++;
					$BovenGesprongen = TRUE;
				}
				if ($rij['Verbinding']<>$ouderij['Verbinding'] or $BovenGesprongen) {
					$huidiglevel = naarlevel(1,$huidiglevel);
					$kml[] = '<Folder>';
					$kml[] = '<name>'. htmlspecialchars($rij['Verbinding'], ENT_QUOTES,"ISO-8859-1", true) . '</name>';
					$huidiglevel++;
					$BovenGesprongen = TRUE;
				}
				$rij['ID'] = 'm'. $rij['ID'];
				$kml[] = ' <Placemark id="' . $rij['ID'] . '">';
				$smallname = explode('-',$rij['Naam']);
				$kml[] = ' <name>' . htmlspecialchars(end(explode('-',$rij['Naam'])),ENT_QUOTES,"ISO-8859-1", true) . '</name>';
				if ($expert == 'true') { $kml[] = '  <visibility>0</visibility>'; }
				VoegDataIn($rij);
				$kml[] = ' <styleUrl>#MB' . $rij['Spanning'] . '</styleUrl>';
				$kml[] = ' <Point><coordinates>'. $rij['lon'] . ',' . $rij['lat'] . '</coordinates></Point>';
				$kml[] = ' </Placemark>';
				$ouderij = $rij;
			}
			$huidiglevel = naarlevel(0,$huidiglevel);
		} else {
			$kml[] = '<Folder>';
			$kml[] = '  <name><![CDATA[<i>Nothing to show</i>]]></name>';
			$kml[] = '</Folder>';
		}
	} else {
		$kml[] = '<Folder>';
		$kml[] = '  <name><![CDATA[<i>Zoom in to show</i>]]></name>';
		$kml[] = '</Folder>';
	}
	$kml[] = ' </Folder>';
}
?>