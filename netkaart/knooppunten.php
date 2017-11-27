<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates KML output of   */
/* Knoopunten on the netkaart            */
/* ===================================== */

function TekenKnooppunten($land) {
	global $kml;
	global $range;
	global $sqlbox;
	global $StyleFile;
	global $IconViewHeight;
	global $connection;

	$kml[] = '<Folder>';
	$kml[] = 	'<name>Knooppunten</name>';
	
	// Selecteer alle verbindingen van het betreffende land.
	if ($range < $IconViewHeight){
		$query = 'SELECT *, X( point ) AS lon, Y( point ) AS lat FROM Knooppunten WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND MBRContains(GeomFromText("'.$sqlbox.'"), Knooppunten.point) ORDER BY Spanning DESC, Naam ASC';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));	}
		// ======================= Knooppunten weergeven =======================
		// Selecter alle rijen uit de tabel Stations.
		if (mysqli_num_rows($result)!=0) {
			$ouderij = array('Spanning' => -1 );
			$huidiglevel = 0;
	
			while ($rij = @mysqli_fetch_assoc($result)) {
				if ($rij['Spanning']<>$ouderij['Spanning'])	{
					$huidiglevel = naarlevel(0,$huidiglevel);
					$kml[] = '<Folder>';
					if (intval($rij['Spanning'])=='0'){
						$kml[] = '<name>Voormalig of spanningsloos</name>';
					} else {
						$kml[] = '<name>'. SpanningDecimals($rij['Spanning']) . ' kV ' . htmlspecialchars($rij['ACDC'], ENT_QUOTES,"ISO-8859-1", true) . '</name>';
					}
					$huidiglevel++;
				}
				$rij['ID'] = 'k'. $rij['ID'];
				$kml[] = '<Placemark id="' . $rij['ID'] . '">';
				$kml[] = 	'<name>' . htmlspecialchars($rij['Naam'], ENT_QUOTES,"ISO-8859-1", true) . '</name>';
				VoegDataIn($rij);
				$kml[] = 	'<styleUrl>#' . htmlspecialchars($rij['IconPNG'], ENT_QUOTES,"ISO-8859-1", true). 'knoop</styleUrl>';
				$kml[] = 	'<Point><coordinates>'. $rij['lon'] . ',' . $rij['lat'] . '</coordinates></Point>';
				$kml[] = '</Placemark>';
				$ouderij = $rij;
			}
			$huidiglevel = naarlevel(0,$huidiglevel);
		} else {
			$kml[] = '<Folder>';
			$kml[] = 	'<name><![CDATA[<i>Nothing to show</i>]]></name>';
			$kml[] = '</Folder>';
		}
		
// ====================== Einde Knooppunten weergeven ========================
	} else {
		$kml[] = '<Folder>';
		$kml[] = 	'<name><![CDATA[<i>Zoom in to show</i>]]></name>';
		$kml[] = '</Folder>';
	}
	$kml[] = '</Folder>';
}
?>