<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates KML output of   */
/* verbindingen on the netkaart          */
/* ===================================== */

require('hulpfuncties.php');

function TekenVerbindingen($land) { 
	global $jaarnu;
	global $kml;
	global $range;
	global $sqlbox;
	global $StyleFile;
	global $EHVVoltagesMin;
	global $HVNetViewHeight;
	global $HVVoltagesMin;
	global $MVNetViewHeight;
	global $connection;
	
	// ========================== Verbindingen weergeven ==========================
	// Selecteer alle verbindingen van het betreffende land.
	if ($range > $HVNetViewHeight){
		$query = 'SELECT *, astext(linestring) as lijn FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND Spanning>='. $EHVVoltagesMin . ' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning DESC, ACDC DESC, Beheerder ASC, DeelNet ASC, Hoofdtype DESC, SubType ASC, Naam ASC';
	} 
	if ($range > $MVNetViewHeight AND $range <= $HVNetViewHeight) {
		$query = 'SELECT *, astext(linestring) as lijn FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND Spanning>='. $HVVoltagesMin . ' AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning DESC, ACDC DESC, Beheerder ASC, DeelNet ASC, Hoofdtype DESC, SubType ASC, Naam ASC';
	}
	if ($range <= $MVNetViewHeight) {
		$query = 'SELECT *, astext(linestring) as lijn FROM Verbindingen WHERE JaarInBedrijf<="'. intval(date("Y")) .'" AND JaarUitBedrijf>="'. intval(date("Y")) .'" AND Land="'. $land .'" AND MBRIntersects(GeomFromText("'.$sqlbox.'"), Verbindingen.linestring) ORDER BY Spanning DESC, ACDC DESC, Beheerder ASC, DeelNet ASC, Hoofdtype DESC, SubType ASC, Naam ASC';
	}
	$result = mysqli_query($connection, $query);
	if (!$result) {	die(LogMySQLError(mysqli_error($connection), basename(__FILE__),'Invalid query'));	}
	$kml[] = '<Folder>';
	$kml[] = 	'<name>Hoogspanningsverbindingen</name>';
	
	if (mysqli_num_rows($result)!=0) {
		$ouderij = array('Spanning' => -1, 'ACDC' => '', 'Beheerder' => '', 'DeelNet' => '<leeg>', 'HoofdType' => '');
		$huidiglevel = 0;

		while ($rij = @mysqli_fetch_assoc($result)) {
			$BovenGesprongen = FALSE;
			if ($rij['Spanning']<>$ouderij['Spanning'] OR $rij['ACDC']<>$ouderij['ACDC'] OR $BovenGesprongen)	{
				$huidiglevel = naarlevel(0,$huidiglevel);
				$kml[] = '<Folder>';
				if (intval($rij['Spanning'])=='0'){
					$kml[] = '<name>Spanningsloos</name>';
				} else {
					$kml[] = '<name>'. SpanningDecimals($rij['Spanning']) . ' kV ' . $rij['ACDC'] . '-net</name>';
				}
				$huidiglevel++;
				$BovenGesprongen = TRUE;
			}
			if ($rij['Beheerder']<>$ouderij['Beheerder'] or $BovenGesprongen) {
				$huidiglevel = naarlevel(1,$huidiglevel);
				$kml[] = '<Folder>';
				$kml[] = 	'<name>Concessie '. htmlspecialchars($rij['Beheerder'], ENT_QUOTES, "ISO-8859-1", true) . '</name>';
				$huidiglevel++;
				$BovenGesprongen = TRUE;
			}
			if ($rij['DeelNet']<>$ouderij['DeelNet'] or $BovenGesprongen) {
				if ($rij['DeelNet']<>'') {
					$huidiglevel = naarlevel(2,$huidiglevel);
					$kml[] = '<Folder>';
					$kml[] = 	'<name>Deelnet '. htmlspecialchars($rij['DeelNet'], ENT_QUOTES, "ISO-8859-1", true) . '</name>';
					$huidiglevel++;
					$BovenGesprongen = TRUE;
				}
			}
			if ($rij['HoofdType']<>$ouderij['HoofdType'] or $rij['SubType']<>$ouderij['SubType'] or $BovenGesprongen) {
				if ($rij['DeelNet']<>'') {
					$huidiglevel = naarlevel(3,$huidiglevel);
				} else {
					$huidiglevel = naarlevel(2,$huidiglevel);
				}
				$kml[] ='<Folder>';
				if ($rij['HoofdType']=='Kabel') {
					$kml[] = '<name>'. $rij['SubType'] . 'kabels</name>';
				} else {
					$kml[] = '<name>Luchtlijnen</name>';
				}
				$huidiglevel++;
				$BovenGesprongen = TRUE;
			}
			$rij['ID'] = 'v'. $rij['ID'];
			$kml[] = '<Placemark id="' . $rij['ID'] . '">';
			$kml[] = '<name>' . htmlspecialchars($rij['Naam'], ENT_QUOTES, "ISO-8859-1", true)  . '</name>';
			$rij['Lengte'] = number_format(greatCircleLength($rij['lijn'])/1000, 2, ',', '.'); 
			VoegDataIn($rij);
			if ($rij['HoofdType']=='Kabel') {
				$kml[] = '<styleUrl>#' . $rij['Spanning'] . 'K</styleUrl>';
			} else {
				$kml[] = '<styleUrl>#'. $rij['Spanning'] . 'L</styleUrl>';
			}
			$temp = stripdecimals(get_string_between($rij['lijn'],'(',')'));
			$temp = '<LineString><tessellate>1</tessellate><gx:drawOrder>' . intval($rij['Spanning']) . '</gx:drawOrder><coordinates>'.$temp;
			$rij['lijn'] = $temp. '</coordinates></LineString>';
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
	$kml[] = ' </Folder>';
// ====================== Einde Verbindingen weergeven ========================
}
?>