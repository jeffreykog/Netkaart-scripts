<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates KML output of   */
/* Stationsiconen on the netkaart        */
/* ===================================== */

function TekenStationsIconen($land) {
	global $kml;
	global $range;
	global $sqlbox;
	global $StyleFile;
	global $IconViewHeight;
	global $connection;

	$kml[] = '<Folder>';
	$kml[] = 	'<name>Stationsiconen</name>';
	
		// ========================== Stationsiconen weergeven ==========================
	// Selecteer alle stationsiconen van het betreffende land.
	if ($range < $IconViewHeight){
		$query = 'SELECT *, X( point ) AS lon, Y( point ) AS lat FROM Stationsiconen WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND MBRContains(GeomFromText("'.$sqlbox.'"), Stationsiconen.point) ORDER BY Spanning DESC, Spanningen ASC, Naam ASC';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));	}
		// ======================= Stationsiconen weergeven =======================
		// Selecter alle rijen uit de tabel Stations.
		if (mysqli_num_rows($result)!=0) {
			$ouderij = array('Spanning' => -1, 'Spanningen' => '' );
			$huidiglevel = 0;
			while ($rij = @mysqli_fetch_assoc($result)) {
				$BovenGesprongen = FALSE;
				if ($rij['Spanning']<>$ouderij['Spanning'])	{
					$huidiglevel = naarlevel(0,$huidiglevel);
					$kml[] = '<Folder>'; 
					if (intval($rij['Spanning'])=='0'){
						$kml[] = '<name>Spanningsloos / buiten gebruik</name>';
					} else {
						$kml[] = '<name>'. SpanningDecimals($rij['Spanning']) . ' kV ' . htmlspecialchars($rij['ACDC'], ENT_QUOTES) . '</name>';
					}
					$huidiglevel++;
					$BovenGesprongen = TRUE;
				}
				if ($rij['Spanningen']<>$ouderij['Spanningen'] or $BovenGesprongen) {
					$huidiglevel = naarlevel(1,$huidiglevel);
					$kml[] = '<Folder>';
					$kml[] = '<name>'. htmlspecialchars($rij['Spanningen'], ENT_QUOTES) . '</name>';
					$huidiglevel++;
					$BovenGesprongen = TRUE;
				}
				$rij['ID'] = 's'. $rij['ID'];
				$kml[] = '<Placemark id="' . $rij['ID'] . '">';
				$kml[] = '<name>' . htmlspecialchars($rij['Naam'],ENT_QUOTES,"ISO-8859-1", true) . '</name>';
				VoegDataIn($rij);
				$kml[] = '<styleUrl>#label' . htmlspecialchars($rij['Spanningen'], ENT_QUOTES) . 'stijl</styleUrl>';
				$kml[] = '<Point><coordinates>'. $rij['lon'] . ',' . $rij['lat'] . '</coordinates></Point>';
				$kml[] = '</Placemark>';
				$ouderij = $rij;
			} 
			$huidiglevel = naarlevel(0,$huidiglevel);
		} else {
			$kml[] = '<Folder>';
			$kml[] = 	'<name><![CDATA[<i>Nothing to show</i>]]></name>';
			$kml[] = '</Folder>';
		}
		
// ====================== Einde Stationsiconen weergeven ========================
	} else {
		$kml[] = '<Folder>';
		$kml[] = 	'<name><![CDATA[<i>Zoom in to show</i>]]></name>';
		$kml[] = '</Folder>';
	}
	$kml[] = '</Folder>';
}
?>