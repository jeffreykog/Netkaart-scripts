<?php

/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script defines global variables, */
/* heights which govern what is shown    */
/* And a checklogin function             */
/* ===================================== */

 require_once ('/home/deb99417/domains/hoogspanningsnet.com/netkaart_config/database_login.php');
 
 $IconDir 		= 'files/';
 $UseDir 		= '';
 $WebServer 		= 'https://netkaart.hoogspanningsnet.com/';
 $CommonFileDir		= 'commonfiles/';
 $StyleFile 		= 'stijlen.txt';
 $scriptnaam		= 'GE-kaart';
 $scriptversie	= 'V1.2';
 $copyrightstring    = '(c)2017 www.hoogspanningsnet.com';
 $KaartVersie		= 'Versie 6.0';
 $KaartDatum		= 'mei 2017';
 $KaartNaam		= 'Pocket';
 $KaartCredits	= '<p>Sommige rechten voorbehouden. Bezoek het <a href="http://www.hoogspanningsnet.com/google-earth/">netkaartenportal</a> of het '.
			  '<a href="http://www.hoogspanningsforum.com/viewforum.php?f=13">netkaartsubforum</a> op onze site voor extra informatie, '.
			  'uitgebreide documentatie, feedback en meer.</p>'.
			  '<p><u>Verantwoording:</u>'.
			  '<p>Projectleiding en aanspreekpunt: H. Nienhuis<br>'.
			  'Techniek: B. van Duijnhoven<br>'.
			  'Eindtoezicht: B. Lens (BE), H. Nienhuis (NL excl. 50 kV), O. Lesley (NL 50 kV), B. van Duijnhoven (FR).<br>'.
			  '<p>Intekeningen en bijdrages: F. Arnold, H. Bruin, B. van Duijnhoven, P. Forbes, H. Heikoop, B. Lens, O .Lesley,  P. Lewis, M. van der Meer, G. Nachbar, '.
			  'O. Nielsen, H. Nienhuis, P. Schokkenbroek, B. Sondaar, J. Swank en andere bijdragers via de site, het forum of via het insturen van feedback.</p>'.
			  'Deze netkaart bevat informatie uit de database van <a href="http://www.openstreetmap.org">www.openstreetmap.org</a>, deze wordt beschikbaargesteld onder de <a href="http://opendatacommons.org/licenses/odbl/1.0/">Open Database Licence (ODbL)</a>.</p>'.
			  '<p><img src="http://www.hoogspanningsnet.com/wp-content/uploads/Creative-Commons-icons.png"/>'.
			  '<p><i>For information in English, please visit <a href="http://www.hoogspanningsnet.com/about/english/">this page</a> '.
			  'on our website to find more information about us and this grid map.</i>';
 $PerformanceTiming  = FALSE;
 $PerformanceFile    = 'PerfLog.txt';

 
 $EHVVoltagesMin	= 250;  		// Vanaf deze spanning worden de verbindingen altijd weergegeven
 $HVVoltagesMin	= 110;			// Deze spanningen tm $EHVVoltagesMin worden weergegeven vanaf $HVNetViewHeight
 $MVVoltagesMin	= 20;			// Deze spanningen tm $HVVoltagesMin worden weergegeven vanaf $MVNetViewHeight
 $HVNetViewHeight 	= 500000;		// Onder deze hoogte worden verbindingen met een spanning tussen $HVVoltagesMin en $EHVVoltagesMin ook weergegeven
 $MVNetViewHeight 	= 250000;		// onder deze hoogte worden verbindingen met een spanning lager dan $HVVoltagesMin ook weergegeven
 $IconViewHeight 	= 150000;		// Onder deze hoogte worden de stationsiconen, netopeningen, knooppunten etc weergegeven
 $MastViewHeight 	= 15000;		// Onder deze hoogte worden masten weergegeven
 $TerrViewHeight 	= 20000;		// Onder deze hoogte worden de terreinmarkeringen weergegeven
 $MastLijnAfstand	= 20;			// Maximale afstand tussen mast en lijn

 // Weer te geven velden in de balloons =========================================================================//
 
 $VerbindingenDisplay = array (
 		array ('DisplayNaam' => '', 						'Column'=>'HoofdType', 						'Type'=>'Func',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'ID', 						'Column'=>'ID', 							'Type'=>'Link',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Lengte', 					'Column'=>'Lengte', 						'Type'=>'Func', 	'Suffix'=>	'km',	'FuncName'=>'DisplayLengte'),
 		array ('DisplayNaam' => 'Type', 					'Column'=>'SubType', 						'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVerbSubtype'),
 		array ('DisplayNaam' => 'Bedrijfsspann.', 			'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Ontwerpspann.', 			'Column'=>'MaxSpanning', 					'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Land - Deelnet', 			'Column'=>'Land,DeelNet', 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVerbDeelnet'),
 		array ('DisplayNaam' => 'Capaciteit', 				'Column'=>'Capaciteit', 					'Type'=>'Func', 	'Suffix'=>	'MVA', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
 		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;naam',	 	'Column'=>'BeheerderNaam', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;code', 		'Column'=>'BeheerderCode', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
 );
 
 $StationsiconenDisplay = array (
 		array ('DisplayNaam' => 'Station',					'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'ID', 						'Column'=>'ID', 							'Type'=>'Link',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Afkorting', 				'Column'=>'Afkorting',						'Type'=>'Small',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Type', 					'Column'=>'SubType', 						'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Spanningen', 				'Column'=>'Spanningen', 					'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
 		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'',		 						'Type'=>'Blanco', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;primair', 	'Column'=>'BeheerderPrim', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;Secundair', 	'Column'=>'BeheerderSec', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
 );
 
 $MastenDisplay = array (
 		array ('DisplayNaam' => 'Hoogspanningsmast', 		'Column'=>'',		 						'Type'=>'Title',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'ID', 						'Column'=>'ID', 							'Type'=>'Link',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'Mastnummer', 				'Column'=>'Naam', 							'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Verbinding', 				'Column'=>'Verbinding',						'Type'=>'Small',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Bouwwijze', 				'Column'=>'HoofdType', 						'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Model', 					'Column'=>'SubType',		 				'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Functie', 					'Column'=>'Functie',		 				'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Benutting', 				'Column'=>'Benutting',		 				'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Hoogte', 					'Column'=>'Hoogte',		 					'Type'=>'Small', 	'Suffix'=>	'meter'),
 		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
 		array ('DisplayNaam' => 'Circuits', 				'Column'=>'Circuits',		 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayCircuits' ),
 		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
 );
 
 $StationsterreinDisplay = array (
 		array ('DisplayNaam' => 'Hoogspanningsstation', 	'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'ID', 						'Column'=>'ID', 							'Type'=>'Link',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Type', 					'Column'=>'SubType', 						'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayTerrSubtype'),
 		array ('DisplayNaam' => 'Bedrijfsspann.', 			'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Ontwerpspann.', 			'Column'=>'OntwerpSpanning', 				'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Capaciteit', 				'Column'=>'Capaciteit',		 				'Type'=>'Func', 	'Suffix'=>	'A', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
 		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;naam',	 	'Column'=>'BeheerderNaam', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => '&nbsp;-&nbsp;code', 		'Column'=>'BeheerderCode', 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Aantal velden', 			'Column'=>'AantalVelden',		 			'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
 );
 
 $KnooppuntenDisplay = array (
 		array ('DisplayNaam' => 'Netknooppunt', 			'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'ID', 						'Column'=>'ID', 							'Type'=>'Link',		'Suffix'=>	''),
 		array ('DisplayNaam' => 'Type', 					'Column'=>'SubType', 						'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
 		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplaySpanning'),
 		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Small', 	'Suffix'=>	''),
 		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
 		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
 );
 
// Weer te geven velden in de balloons =========================================================================//
 
// Functions ===================================================================================================//

function CheckLoggedIn($beheerder) {
	// Opens a connection to a MySQL server.
	global $username;
	global $server;
	global $password;
	global $database;
	
	$connection = mysqli_connect ($server, $username, $password, $database);
	if ($connection->connect_errno) {
  	exit ("Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error);
	}
	
	$loggedin = false;

	if (isset($_COOKIE['editusername']) && isset($_COOKIE['editpassword'])) {
		If ($beheerder==TRUE) {
		$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_COOKIE['editusername'].'" AND WachWoord="'.$_COOKIE['editpassword'].'" AND Role="Beheerder"';
		} else {
			$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_COOKIE['editusername'].'" AND WachWoord="'.$_COOKIE['editpassword'].'"';
		}
		$result=mysqli_query($connection, $sql);
		// Mysql_num_row is counting table row
		$count=mysqli_num_rows($result);
		// If result matched $myusername and $mypassword, table row must be 1 row
		if($count==1){
			$loggedin=true;
		} else {
			$loggedin = false;
		}
	}
	return $loggedin;
}

function LogMySQLError ($mysqlerror, $phpfile, $errtxt) {
	global $scriptnaam;
	global $scriptversie;
	
	$LogLine = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']).  ';' . $_SERVER["REMOTE_ADDR"] . ';' . $scriptnaam. ' ' .$scriptversie. ';' . $phpfile .';' .$mysqlerror. "\n";
	file_put_contents('beheer/MySQLErrors.csv' , $LogLine , FILE_APPEND | LOCK_EX);
	return $errtxt;
}

?>