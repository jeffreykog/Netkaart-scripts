<?php

/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* For the webkaart :                    */
/* This script defines global variables, */
/* heights which govern what is shown    */
/* And a checklogin function             */
/* ===================================== */

// Settings ===================================================================================================//

	require_once ('/home/deb99417/domains/hoogspanningsnet.com/netkaart_config/webkaart_login.php');
	
	$WebServer 			= 'https://webkaart.hoogspanningsnet.com/';
	$IconDir 			= 'files/';
	$UseDir 			= '';
	$CommonFileDir		= 'commonfiles/';
	$scriptnaam			= 'Webkaart';
	$scriptversie		= 'V1.0';
	$copyrightstring	= '(c)2017 www.hoogspanningsnet.com';
	$KaartTitel			= 'HoogspanningsNet Netkaart';
	$KaartVersie		= '6.0';
	$KaartDatum			= 'Mei 2017';
	$KaartNaam			= 'Pocket';

	$EHVVoltagesMin		= 245;  		// Vanaf deze spanning worden de verbindingen altijd weergegeven
	$HVVoltagesMin		= 145;			// Deze spanningen tm $EHVVoltagesMin worden weergegeven vanaf $HVNetZoomLevel
	$MVVoltagesMin		= 30;			// Deze spanningen tm $HVVoltagesMin worden weergegeven vanaf $MVNetZoomLevel

	$EHVIconMin			= 170;			// Deze spanningen tm $HVVoltagesMin worden weergegeven vanaf $MVNetZoomLevel
	$HVIconMin			= 72;			// Deze spanningen tm $HVVoltagesMin worden weergegeven vanaf $MVNetZoomLevel
	$MVIconMin			= 30;			// Deze spanningen tm $HVVoltagesMin worden weergegeven vanaf $MVNetZoomLevel

	$MastLijnAfstand	= 14;			// Maximale afstand tussen mast en lijn
 
	$HVNetZoomLevel 	= 8;			// Onder dit zoom level worden verbindingen met een spanning tussen $HVVoltagesMin en $EHVVoltagesMin ook weergegeven
	$MVNetZoomLevel 	= 10;			// onder deze hoogte worden verbindingen met een spanning lager dan $MVVoltagesMin ook weergegeven
 
	$EHVIconZoomLevel 	= 9;			// Onder deze hoogte worden de stationsiconen, netopeningen, knooppunten etc tussen $HVIconMin en $EHVIconMin ook weergegeven
	$HVIconZoomLevel 	= 11;			// Onder deze hoogte worden de stationsiconen, netopeningen, knooppunten etc tussen $HVIconMin en $EHVIconMin ook weergegeven
	$MVIconZoomLevel 	= 12;			// Onder deze hoogte worden de stationsiconen, netopeningen, knooppunten etc lager dan $MVIconsMin ook weergegeven

	$MastZoomLevel 		= 13;			// Onder deze hoogte worden masten weergegeven
	$TerrZoomLevel 		= 13;			// Onder deze hoogte worden de terreinmarkeringen weergegeven
	$KnppZoomLevel 		= 13;			// Onder deze hoogte worden de knooppunten weergegeven

// Weer te geven velden in de balloons =========================================================================//

	$VerbindingenDisplay = array (	
		array ('DisplayNaam' => 'Verbinding', 				'Column'=>'', 								'Type'=>'Func',		'Suffix'=>	'',		'FuncName'=>'DisplayTitle'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Deelnet', 					'Column'=>'DeelNet', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Constructiewijze',			'Column'=>'HoofdType,SubType', 				'Type'=>'Func', 	'Suffix'=>	'',		'FuncName'=>'DisplayVerbHoofdtype'),
		array ('DisplayNaam' => 'Systeem,Frequentie', 		'Column'=>'ACDC,Systeem,Frequentie', 		'Type'=>'Func', 	'Suffix'=>	'Hz',	'FuncName'=>'DisplaySysteemFreq'),
		array ('DisplayNaam' => 'Bedrijfsspanning', 		'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplaySpanning'),
		array ('DisplayNaam' => 'Ontwerpspanning', 			'Column'=>'MaxSpanning', 					'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplayOntwerpSpan'),
		array ('DisplayNaam' => 'Hoogste spanning', 		'Column'=>'HoogsteSpanning', 				'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplayHoogsteSpan'),
		array ('DisplayNaam' => 'Transportcapaciteit', 		'Column'=>'Capaciteit', 					'Type'=>'Func', 	'Suffix'=>	'MVA',	'FuncName'=>'DisplayCapaciteit'),
		array ('DisplayNaam' => 'Lengte', 					'Column'=>'Lengte', 						'Type'=>'Func', 	'Suffix'=>	'km',	'FuncName'=>'DisplayLengte'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdercode', 			'Column'=>'BeheerderCode', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdernummer', 			'Column'=>'BeheederNummer', 				'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
	);

	$StationsiconenDisplay = array (	
		array ('DisplayNaam' => 'Station',					'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Type', 					'Column'=>'SubType', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Afkorting', 				'Column'=>'Afkorting',						'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Spanningen', 				'Column'=>'Spanningen', 					'Type'=>'Func', 	'Suffix'=>	'kV', 	'FuncName'=>'DisplayIcoonSpanningen'),
		array ('DisplayNaam' => 'Beheerder primair', 		'Column'=>'BeheerderPrim', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerder secundair', 		'Column'=>'BeheerderSec', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
	);

	$MastenDisplay = array (
		array ('DisplayNaam' => 'Hoogspanningsmast', 		'Column'=>'',		 						'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Verbinding', 				'Column'=>'Verbinding',						'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Mastnummer', 				'Column'=>'Naam', 							'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Bouwwijze', 				'Column'=>'HoofdType', 						'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVertaald'),
		array ('DisplayNaam' => 'Mastmodel', 				'Column'=>'MastModel',		 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVertaald'),
		array ('DisplayNaam' => 'Subtype', 					'Column'=>'SubType',		 				'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Mastfunctie', 				'Column'=>'Functie',		 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVertaald'),
		array ('DisplayNaam' => 'Masthoogte', 				'Column'=>'Hoogte',		 					'Type'=>'Func', 	'Suffix'=>	'meter', 'FuncName'=>'DisplayMastHoogte'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Benutting', 				'Column'=>'Benutting',		 				'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Circuits', 				'Column'=>'Circuits',		 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayCircuits' ),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
	);
	
	$StationsterreinDisplay = array (
		array ('DisplayNaam' => 'Hoogspanningsstation', 	'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Constructiewijze', 		'Column'=>'HoofdType', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Railconfiguratie', 		'Column'=>'SubType', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Systeem,Frequentie', 		'Column'=>'ACDC,Systeem,Frequentie', 		'Type'=>'Func', 	'Suffix'=>	'Hz',	'FuncName'=>'DisplaySysteemFreq'),
		array ('DisplayNaam' => 'Bedrijfsspanning', 		'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplaySpanning'),
		array ('DisplayNaam' => 'Ontwerpspanning', 			'Column'=>'OntwerpSpanning',				'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplayOntwerpSpan'),
		array ('DisplayNaam' => 'Hoogste spanning', 		'Column'=>'HoogsteSpanning', 				'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplayHoogsteSpan'),
		array ('DisplayNaam' => 'Railcapaciteit', 			'Column'=>'Capaciteit',		 				'Type'=>'Func', 	'Suffix'=>	'A',	'FuncName'=>'DisplayCapaciteit'),
		array ('DisplayNaam' => 'Aantal velden', 			'Column'=>'AantalVelden',		 			'Type'=>'Func', 	'Suffix'=>	'',		'FuncName'=>'DisplayVelden'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdernaam',		 	'Column'=>'BeheerderNaam', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdercode', 			'Column'=>'BeheerderCode', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 					'Type'=>'Big', 		'Suffix'=>	''),
	);	
	$VeldTypen = array ('Lijnveld', 'Kabelveld', 'Generatorveld', 'Transformatorveld', 'Koppelveld', 'Spoelveld', 'Condensatorveld', 'overig');

	$KnooppuntenDisplay = array (
		array ('DisplayNaam' => 'Net knooppunt',			'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Netsituatie', 				'Column'=>'SubType', 						'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayVertaald'),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplaySpanning'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'BeheerderPrim', 					'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
	);
	
	$OpeningenDisplay = array (
		array ('DisplayNaam' => 'Net opening',				'Column'=>'HoofdType', 						'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplaySpanning'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
	);

	$BedrijfsmiddelenDisplay = array (
		array ('DisplayNaam' => 'Bedrijfsmiddel',			'Column'=>'', 								'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam', 							'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 					'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLandnaam'),
		array ('DisplayNaam' => 'Objecttype', 				'Column'=>'HoofdType', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Subtype', 					'Column'=>'SubType', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 						'Type'=>'Func', 	'Suffix'=>	'kV',	'FuncName'=>'DisplaySpanning'),
		array ('DisplayNaam' => 'Systeem,Frequentie', 		'Column'=>'ACDC,Systeem,Frequentie', 		'Type'=>'Func', 	'Suffix'=>	'Hz',	'FuncName'=>'DisplaySysteemFreq'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 						'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf,JaarUitBedrijf', 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayInbedrijf'),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 						'Type'=>'Big', 		'Suffix'=>	''),
	);
	
// Te bewerken velden in edititem ==============================================================================//
	
	$VerbindingenEdit = array (
		array ('DisplayNaam' => 'Verbinding', 				'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Deelnet', 					'Column'=>'DeelNet',			'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Constructiewijze',			'Column'=>'HoofdType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Lijn', 'Kabel')),
		array ('DisplayNaam' => 'Subtype', 					'Column'=>'SubType', 			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('', 'Grond', 'Zee', 'Supergeleidend')),
		array ('DisplayNaam' => 'AC/DC',	 				'Column'=>'ACDC',		 		'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('AC', 'DC')),
		array ('DisplayNaam' => 'Systeem',	 				'Column'=>'Systeem',		 	'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('Driefasen', 'Tweefasen', 'Eenfase', 'Monopool aardretour', 'Monopool geleiderretour', 'Symmetrische monopool', 'Bipool', 'anders/onbekend')),
		array ('DisplayNaam' => 'Netfrequentie',			'Column'=>'Frequentie',		 	'Type'=>'Pulldown', 'Suffix'=>	'Hz', 	'AllowedValues'=> array ('50', '60', '16,7', '0')),
		array ('DisplayNaam' => 'Bedrijfsspanning', 		'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Ontwerpspanning', 			'Column'=>'MaxSpanning', 		'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Hoogste spanning', 		'Column'=>'HoogsteSpanning', 	'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Transportcapaciteit', 		'Column'=>'Capaciteit',			'Type'=>'Small', 	'Suffix'=>	'MVA', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 			'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdercode', 			'Column'=>'BeheerderCode', 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdernummer', 			'Column'=>'BeheederNummer', 	'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$StationsiconenEdit = array (
		array ('DisplayNaam' => 'Hoogspanningsstation', 	'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Type',						'Column'=>'SubType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Trafostation', 'Koppelstation', 'Schakelstation', 'Frequentie omvormer', 'Converter', 'B2B Converter', 'Klantstation', 'Productie', 'anders/onbekend')),
		array ('DisplayNaam' => 'Afkorting',				'Column'=>'Afkorting',			'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Systeem',	 				'Column'=>'ACDC',		 		'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('AC', 'DC', 'AC/DC')),
		array ('DisplayNaam' => 'Hoogste spanning', 		'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Spanningen', 				'Column'=>'Spanningen', 		'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckSpanningen'),
		array ('DisplayNaam' => 'Beheerder primair', 		'Column'=>'BeheerderPrim',		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerder secundair', 		'Column'=>'BeheerderSec', 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 			'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$MastenEdit = array (
		array ('DisplayNaam' => 'Hoogspanningsmast', 		'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Verbinding', 				'Column'=>'Verbinding',			'Type'=>'Small',	'Suffix'=>	''),
		array ('DisplayNaam' => 'Mastnummer', 				'Column'=>'Naam', 				'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Bouwwijze', 				'Column'=>'HoofdType', 			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Vakwerkmast', 'Buismast', 'Hybridemast', 'Betonnen mast', 'Houten mast', 'anders/onbekend' )),
		array ('DisplayNaam' => 'Mastmodel', 				'Column'=>'MastModel',		 	'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('Donaumast', 'Hamerkopmast', 'Tweevlaksmast', 'Drievlaksmast', 'Viervlaksmast', 'Dennenboommast', 'Dubbelvlagmast', 'Deltamast', 'Portaalmast', 'Tonmast', 'Bipolemast', 'Driehoeksmast', 'Vlagmast', 'Asymmetrisch enkelcircuitmast', 'Eengeleidermast', 'Gaffelmast', 'Schoormast', 'Combinatiemast', 'anders/onbekend')),
		array ('DisplayNaam' => 'Subtype', 					'Column'=>'SubType',		 	'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Mastfunctie', 				'Column'=>'Functie',		 	'Type'=>'Pulldown',	'Suffix'=>	'',		'AllowedValues'=> array ('Steunmast', 'Hoekmast', 'Afspanmast', 'Wisselmast', 'Aftakmast', 'Splitsingsmast', 'Overkruisingsmast', 'Eindmast', 'Stijgjuk', 'anders/onbekend' )),
		array ('DisplayNaam' => 'Masthoogte', 				'Column'=>'Hoogte',		 		'Type'=>'Small', 	'Suffix'=>	'm', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'AC/DC', 					'Column'=>'ACDC', 				'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckACDC'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 			'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Benutting', 				'Column'=>'Benutting',		 	'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Circuits', 				'Column'=>'Circuits',		 	'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckMastCircuits' ),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$StationsterreinEdit = array (
		array ('DisplayNaam' => 'Station', 					'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Constructiewijze',			'Column'=>'HoofdType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Hangende rails', 'Staande rails', 'Gasgeïsoleerd', 'Hybride geïsoleerd', 'anders/onbekend')),
		array ('DisplayNaam' => 'Railconfiguratie',			'Column'=>'SubType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Dubbelrail', 'Enkelrail', 'Enkelrail - langskoppeling', 'Dubbelrail U-I', 'Dubbelrail met bypassrail', 'Drierail', 'Ringrail', 'anders/onbekend')),
		array ('DisplayNaam' => 'AC/DC',	 				'Column'=>'ACDC',		 		'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('AC', 'DC')),
		array ('DisplayNaam' => 'Systeem',	 				'Column'=>'Systeem',		 	'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('Driefasen', 'Tweefasen', 'Eenfase', 'Monopool aardretour', 'Monopool geleiderretour', 'Symmetrische monopool', 'Bipool', 'anders/onbekend')),
		array ('DisplayNaam' => 'Netfrequentie',			'Column'=>'Frequentie',		 	'Type'=>'Pulldown', 'Suffix'=>	'Hz', 	'AllowedValues'=> array ('50', '60', '16,7', '0')),
		array ('DisplayNaam' => 'Bedrijfsspanning', 		'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Ontwerpspanning', 			'Column'=>'OntwerpSpanning', 	'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Hoogste spanning', 		'Column'=>'HoogsteSpanning', 	'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Railcapaciteit', 			'Column'=>'Capaciteit',			'Type'=>'Small', 	'Suffix'=>	'A', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder', 			'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdernaam', 			'Column'=>'BeheerderNaam', 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Beheerdercode', 			'Column'=>'BeheerderCode', 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Aantal velden', 			'Column'=>'AantalVelden',		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$KnooppuntenEdit = array (
		array ('DisplayNaam' => 'Net knooppunt',			'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Netsituatie',				'Column'=>'SubType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Opstijgpunt', 'Harde aftak', 'Schakelbare aftak', 'Inlussing', 'Splitsing', 'Overkruising', 'Elektrodepunt', 'Gecombineerde situatie', 'anders/onbekend')),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'BeheerderPrim',		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 			'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$OpeningenEdit = array (
		array ('DisplayNaam' => 'Netopening',				'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerking', 			'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	$BedrijfsmiddelenEdit = array (
		array ('DisplayNaam' => 'Bedrijfsmiddel',			'Column'=>'',		 			'Type'=>'Title',	'Suffix'=>	''),
		array ('DisplayNaam' => 'ID', 						'Column'=>'ID',		 			'Type'=>'Func',		'Suffix'=>	'', 	'FuncName'=>'DisplayID'),
		array ('DisplayNaam' => 'Naam', 					'Column'=>'Naam',		 		'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Land', 					'Column'=>'Land',		 		'Type'=>'Small', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckLand'),
		array ('DisplayNaam' => 'Objecttype',				'Column'=>'HoofdType',			'Type'=>'Pulldown', 'Suffix'=>	'', 	'AllowedValues'=> array ('Transformator', 'Condensatorbank', 'Compensatiespoel', 'TF-apparatuur', 'Frequentie-omvormer', 'Converter', 'Communicatietoren', 'Bedrijfscentrum', 'anders/onbekend')),
		array ('DisplayNaam' => 'Subtype', 					'Column'=>'SubType',		 	'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'Spanning', 				'Column'=>'Spanning', 			'Type'=>'Small', 	'Suffix'=>	'kV', 	'CheckFuncName'=>'CheckNumber'),
		array ('DisplayNaam' => 'AC/DC',	 				'Column'=>'ACDC',		 		'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('AC', 'DC')),
		array ('DisplayNaam' => 'Systeem',	 				'Column'=>'Systeem',		 	'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('Driefasen', 'Tweefasen', 'Eenfase', 'Monopool aardretour', 'Monopool geleiderretour', 'Symmetrische monopool', 'Bipool', 'anders/onbekend')),
		array ('DisplayNaam' => 'AC/DC',	 				'Column'=>'ACDC',		 		'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('AC', 'DC')),
		array ('DisplayNaam' => 'Systeem',	 				'Column'=>'Systeem',		 	'Type'=>'Pulldown', 'Suffix'=>	'',		'AllowedValues'=> array ('Driefasen', 'Tweefasen', 'Eenfase', 'Monopool aardretour', 'Monopool geleiderretour', 'Symmetrische monopool', 'Bipool', 'anders/onbekend')),
		array ('DisplayNaam' => 'Beheerder', 				'Column'=>'Beheerder',			'Type'=>'Small', 	'Suffix'=>	''),
		array ('DisplayNaam' => 'In dienst', 				'Column'=>'JaarInBedrijf', 		'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Uit dienst', 				'Column'=>'JaarUitBedrijf', 	'Type'=>'Date', 	'Suffix'=>	'', 	'CheckFuncName'=>'CheckDatum'),
		array ('DisplayNaam' => 'Foto URL', 				'Column'=>'FotoURL',		 	'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayFotoURL', 	'CheckFuncName'=>'CheckFotoURL' ),
		array ('DisplayNaam' => 'Bron(nen)', 				'Column'=>'Bron',		 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Opmerkingen', 				'Column'=>'Opmerkingen', 		'Type'=>'Big', 		'Suffix'=>	''),
		array ('DisplayNaam' => 'Wijzingen', 				'Column'=>'log', 				'Type'=>'Func', 	'Suffix'=>	'', 	'FuncName'=>'DisplayLog' ),
	);
	
	
// Functions ===================================================================================================//
require_once($CommonFileDir.'class.translator.php');
	
function vertaal($str){
	global $translate;
	$vert = $translate->__($str);
	return $vert;
}
	
	
function CheckLoggedIn($beheerder) {
	// Opens a connection to a MySQL server.
	global $username;
	global $server;
	global $password;
	global $database;
	
	$loggedin = false;

	if (isset($_COOKIE['editusername']) && isset($_COOKIE['editpassword'])) {
		If ($beheerder==TRUE) {
			$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_COOKIE['editusername'].'" AND WachWoord="'.$_COOKIE['editpassword'].'" AND Role="Beheerder"';
		} else {
			$sql='SELECT * FROM beheerders WHERE GebrNaam="'.$_COOKIE['editusername'].'" AND WachWoord="'.$_COOKIE['editpassword'].'"';
		}
		$connection = mysqli_connect ($server, $username, $password, $database);
		if ($connection->connect_errno) { exit (LogMySQLError("Failed to connect to MySQL", mysqli_connect_error())); }
		
		$result=mysqli_query($connection, $sql);
		
		//Close connection to MySQL server
		mysqli_close($connection);
		
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
	file_put_contents('../netkaart/beheer/MySQLErrors.csv' , $LogLine , FILE_APPEND | LOCK_EX);
	return $errtxt;
}
?>