<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates KML output of   */
/* Terreinmarkeringen on the netkaart    */
/* ===================================== */

function TekenTerreinMarkeringen($land) {
	global $kml;
	global $range;
	global $sqlbox;
	global $StyleFile;
	global $TerrViewHeight;
	global $connection;

	$kml[] = '<Folder>';
	$kml[] = 	'<name>Terreinmarkeringen</name>';
	
	// Selecteer alle terreinmarkeringen van het betreffende land.
	if ($range < $TerrViewHeight){
		$query = 'SELECT *, astext(polygon) as lijn FROM Stations WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Stations.polygon) ORDER BY Spanning DESC, Naam ASC';
		$result = mysqli_query($connection, $query);
		if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));	}
		// ======================= Terreinmarkeringen weergeven =======================
		// Selecter alle rijen uit de tabel Stations.
		if (mysqli_num_rows($result)!=0) {
			$ouderij = array('Spanning' => -1 );
			$huidiglevel = 0;

			while ($rij = @mysqli_fetch_assoc($result)) {
				if ($rij['Spanning']<>$ouderij['Spanning'])	{
					$huidiglevel = naarlevel(0,$huidiglevel);
					$kml[] = '<Folder>';
					if (intval($rij['Spanning'])=='0'){
						$kml[] = '<name>Spanningsloos / buiten gebruik</name>';
					} else {
						$kml[] = '<name>'. SpanningDecimals($rij['Spanning']) . ' kV ' . htmlspecialchars($rij['ACDC'], ENT_QUOTES, "ISO-8859-1", true) . '-stations</name>';
					}
					$huidiglevel++;
				}
				$rij['ID'] = 't'. $rij['ID'];
				$kml[] = '<Placemark id="' . $rij['ID'] . '">';
				$kml[] = 	'<name>' . htmlspecialchars($rij['Naam'], ENT_QUOTES, "ISO-8859-1", true) . '</name>';
				VoegDataIn($rij);
				$kml[] = 	'<styleUrl>#' . $rij['Spanning'] . 'SO</styleUrl>';
				$temp = stripdecimals(get_string_between($rij['lijn'],'((','))'));
				$temp = '<Polygon><outerBoundaryIs><LinearRing><gx:drawOrder>' . intval($rij['Spanning']) . '</gx:drawOrder><coordinates>'.$temp;
				$rij['lijn'] = $temp.'</coordinates></LinearRing></outerBoundaryIs></Polygon>';
				$rij['lijn'] = str_replace(',','_',$rij['lijn']);
				$rij['lijn'] = str_replace(' ',',',$rij['lijn']);
				$rij['lijn'] = str_replace('_',' ',$rij['lijn']);
				$kml[] = $rij['lijn'];
				 
				$kml[] = '</Placemark>';
				$ouderij = $rij;
			}
		$huidiglevel = naarlevel(0,$huidiglevel);
		} else {
			$kml[] = '<Folder>';
			$kml[] = 	'<name><![CDATA[<i>Nothing to show</i>]]></name>';
			$kml[] = '</Folder>';
		}
		
// ====================== Einde Verbindingen weergeven ========================
	} else {
		$kml[] = '<Folder>';
		$kml[] = 	'<name><![CDATA[<i>Zoom in to show</i>]]></name>';
		$kml[] = '</Folder>';
	}
	$kml[] = '</Folder>';
}
?>