<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script contains helper functions */
/* ===================================== */

// ========================== Algemene functies ==========================
function VoegDataIn ($rijtje) {
global $kml;
  	$kml[] = '<ExtendedData>';
  	$kml[] = 	'<Data name="ID">';
  	$kml[] = 		'<value>' . $rijtje['ID'] . '</value>';
  	$kml[] = 	'</Data>';
  	$kml[] = '</ExtendedData>';
}

function naarlevel ($doellevel, $nulevel) {
	global $kml;
	While ($nulevel > $doellevel) {
		$kml[] = '</Folder>';
		$nulevel = $nulevel - 1;
	}
	return $nulevel;
}

function maakkleur ($html,$opac) {
	return strtolower(str_pad(strval(dechex($opac*2.550)),2,'0',STR_PAD_LEFT) . substr($html,4,2) . substr($html,2,2) . substr($html,0,2));
}

function GetBetween($var1="",$var2="",$pool){
	$temp1 = strpos($pool,$var1)+strlen($var1);
	$result = substr($pool,$temp1,strlen($pool));
	$dd=strpos($result,$var2);
	if($dd == 0){
		$dd = strlen($result);
	}
	return substr($result,0,$dd);
}

function greatCircleLength($linestring) {
	$radius = 6378137;
	$length = 0;

	$linestring = GetBetween('LINESTRING(',')',$linestring);
	$points = preg_split('/,/',$linestring);
	for($i=0; $i<count($points)-1; $i++) {
		$point = $points[$i];
		$point = preg_split('/ /',$points[$i]);
		$next_point = preg_split('/ /',$points[$i+1]);
		// Great circle method
		$lat1 = deg2rad($point[1]);
		$lat2 = deg2rad($next_point[1]);
		$lon1 = deg2rad($point[0]);
		$lon2 = deg2rad($next_point[0]);
		$dlon = $lon2 - $lon1;
		$length +=
		$radius *
		atan2(
				sqrt(
						pow(cos($lat2) * sin($dlon), 2) +
						pow(cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dlon), 2)
				)
				,
				sin($lat1) * sin($lat2) +
				cos($lat1) * cos($lat2) * cos($dlon)
		);
	}
	// Returns length in meters.
	return $length;
}

function generateRandomString($length = 15) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

function VindFouteWoorden($string){
	$foutestrings = array("UPDATE ", "DROP ", "SELECT ", "EXEC ", "INSERT ", "DELETE ", "CREATE ", "ALTER ", "TRUNCATE ", "RENAME ", "REPLACE ");
	$uppstring = mb_strtoupper($string);
	foreach($foutestrings as $substr){
		if(strpos($uppstring, mb_strtoupper($substr)) !== FALSE)
		{
			return TRUE; // at least one of the needle strings are substring of heystack, $string
		}
	}
	return FALSE; // no sub_strings is substring of $string.
}

function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function stripdecimals($coordinaten){
	$pieces = explode(",", $coordinaten);
	foreach ($pieces as &$piece) {
		$xys = explode(" ", $piece);
		foreach ($xys as &$coord) {
	   		$temp = explode(".", $coord);
	   		$coord = $temp[0]. '.' .substr($temp[1],0,7);
		}
		$piece = implode(" ", $xys);
	}
	$coordinaten = implode(",", $pieces);
	return $coordinaten;
}

function SpanningDecimals ($spanstr) {
	if (intval(explode('.',$spanstr)[1])==0) {
		return explode('.',$spanstr)[0];
	} else {
		return number_format($spanstr, 1, ',', '.');
	}
}

function country_code_to_country( $code ){
	$code = strtoupper(trim($code));
	$country = '';
	if( $code == 'AF' ) $country = 'Afghanistan';
	if( $code == 'AX' ) $country = 'Aland eilanden';
	if( $code == 'AL' ) $country = 'Albani&euml;';
	if( $code == 'DZ' ) $country = 'Algerije';
	if( $code == 'AS' ) $country = 'Amerikaans Samoa';
	if( $code == 'AD' ) $country = 'Andorra';
	if( $code == 'AO' ) $country = 'Angola';
	if( $code == 'AI' ) $country = 'Anguilla';
	if( $code == 'AQ' ) $country = 'Antarctica';
	if( $code == 'AG' ) $country = 'Antigua en Barbuda';
	if( $code == 'AR' ) $country = 'Argentini&euml;';
	if( $code == 'AM' ) $country = 'Armeni&euml;';
	if( $code == 'AW' ) $country = 'Aruba';
	if( $code == 'AU' ) $country = 'Australi&euml;';
	if( $code == 'AT' ) $country = 'Oostenrijk';
	if( $code == 'AZ' ) $country = 'Azerbaijan';
	if( $code == 'BS' ) $country = 'De Bahamas';
	if( $code == 'BH' ) $country = 'Bahrain';
	if( $code == 'BD' ) $country = 'Bangladesh';
	if( $code == 'BB' ) $country = 'Barbados';
	if( $code == 'BY' ) $country = 'Wit Rusland';
	if( $code == 'BE' ) $country = 'Belgi&euml;';
	if( $code == 'BZ' ) $country = 'Belize';
	if( $code == 'BJ' ) $country = 'Benin';
	if( $code == 'BM' ) $country = 'Bermuda';
	if( $code == 'BT' ) $country = 'Bhutan';
	if( $code == 'BO' ) $country = 'Bolivi&euml;';
	if( $code == 'BA' ) $country = 'Bosnia en Herzegovina';
	if( $code == 'BW' ) $country = 'Botswana';
	if( $code == 'BV' ) $country = 'Bouvet eiland (Bouvetoya)';
	if( $code == 'BR' ) $country = 'Brazili&euml;';
	if( $code == 'IO' ) $country = 'Chagos Archipel';
	if( $code == 'VG' ) $country = 'Britse Maagden eilanden';
	if( $code == 'BN' ) $country = 'Brunei Darussalam';
	if( $code == 'BG' ) $country = 'Bulgarije';
	if( $code == 'BF' ) $country = 'Burkina Faso';
	if( $code == 'BI' ) $country = 'Burundi';
	if( $code == 'KH' ) $country = 'Cambodja';
	if( $code == 'CM' ) $country = 'Cameroen';
	if( $code == 'CA' ) $country = 'Canada';
	if( $code == 'CV' ) $country = 'Cape Verdi&euml;';
	if( $code == 'KY' ) $country = 'Cayman eilanden';
	if( $code == 'CF' ) $country = 'Centraal affrikaanse republiek';
	if( $code == 'TD' ) $country = 'Tjaad';
	if( $code == 'CL' ) $country = 'Chili';
	if( $code == 'CN' ) $country = 'China';
	if( $code == 'CX' ) $country = 'Kertmis eiland';
	if( $code == 'CC' ) $country = 'Cocos (Keeling) Eilanden';
	if( $code == 'CO' ) $country = 'Colombia';
	if( $code == 'KM' ) $country = 'De Comoren';
	if( $code == 'CD' ) $country = 'Congo';
	if( $code == 'CG' ) $country = 'Congo de';
	if( $code == 'CK' ) $country = 'Cook Eilanden';
	if( $code == 'CR' ) $country = 'Costa Rica';
	if( $code == 'CI' ) $country = 'Ivoorkust';
	if( $code == 'HR' ) $country = 'Croati&euml;';
	if( $code == 'CU' ) $country = 'Cuba';
	if( $code == 'CY' ) $country = 'Cyprus';
	if( $code == 'CZ' ) $country = 'Tsjechi&euml;';
	if( $code == 'DK' ) $country = 'Denemarken';
	if( $code == 'DJ' ) $country = 'Djibouti';
	if( $code == 'DM' ) $country = 'Dominica';
	if( $code == 'DO' ) $country = 'Dominicaanse Republiek';
	if( $code == 'EC' ) $country = 'Ecuador';
	if( $code == 'EG' ) $country = 'Egypte';
	if( $code == 'SV' ) $country = 'El Salvador';
	if( $code == 'GQ' ) $country = 'Equatoriaal Guinea';
	if( $code == 'ER' ) $country = 'Eritrea';
	if( $code == 'EE' ) $country = 'Estland';
	if( $code == 'ET' ) $country = 'Ethiopi&euml;';
	if( $code == 'EU' ) $country = 'Europese unie';
	if( $code == 'FO' ) $country = 'Faroe eilanden';
	if( $code == 'FK' ) $country = 'Falkland eilanden';
	if( $code == 'FJ' ) $country = 'Fiji';
	if( $code == 'FI' ) $country = 'Finland';
	if( $code == 'FR' ) $country = 'Frankrijk';
	if( $code == 'GF' ) $country = 'Frans Guiana';
	if( $code == 'PF' ) $country = 'Frans Polynesi&euml;';
	if( $code == 'TF' ) $country = 'Frans Zuidelijke gebieden';
	if( $code == 'GA' ) $country = 'Gabon';
	if( $code == 'GM' ) $country = 'Gambia';
	if( $code == 'GE' ) $country = 'Georgi&euml;';
	if( $code == 'DE' ) $country = 'Duitsland';
	if( $code == 'GH' ) $country = 'Ghana';
	if( $code == 'GI' ) $country = 'Gibraltar';
	if( $code == 'GR' ) $country = 'Griekenland';
	if( $code == 'GL' ) $country = 'Groenland';
	if( $code == 'GD' ) $country = 'Grenada';
	if( $code == 'GP' ) $country = 'Guadeloupe';
	if( $code == 'GU' ) $country = 'Guam';
	if( $code == 'GT' ) $country = 'Guatemala';
	if( $code == 'GG' ) $country = 'Guernsey';
	if( $code == 'GN' ) $country = 'Guinea';
	if( $code == 'GW' ) $country = 'Guinea-Bissau';
	if( $code == 'GY' ) $country = 'Guyana';
	if( $code == 'HT' ) $country = 'Haiti';
	if( $code == 'HM' ) $country = 'Heard eiland and McDonald eilanden';
	if( $code == 'VA' ) $country = 'Vaticaanstad';
	if( $code == 'HN' ) $country = 'Honduras';
	if( $code == 'HK' ) $country = 'Hong Kong';
	if( $code == 'HU' ) $country = 'Hungarije';
	if( $code == 'IS' ) $country = 'IJsland';
	if( $code == 'IN' ) $country = 'India';
	if( $code == 'ID' ) $country = 'Indonesi&euml;';
	if( $code == 'IR' ) $country = 'Iran';
	if( $code == 'IQ' ) $country = 'Irak';
	if( $code == 'IE' ) $country = 'Ierland';
	if( $code == 'IM' ) $country = 'Eiland Man';
	if( $code == 'IL' ) $country = 'Israel';
	if( $code == 'IT' ) $country = 'Itali&euml;';
	if( $code == 'JM' ) $country = 'Jamaica';
	if( $code == 'JP' ) $country = 'Japan';
	if( $code == 'JE' ) $country = 'Jersey';
	if( $code == 'JO' ) $country = 'Jordani&euml;';
	if( $code == 'KZ' ) $country = 'Kazakhstan';
	if( $code == 'KE' ) $country = 'Kenia';
	if( $code == 'KI' ) $country = 'Kiribati';
	if( $code == 'KP' ) $country = 'Korea';
	if( $code == 'KR' ) $country = 'Korea';
	if( $code == 'KW' ) $country = 'Koeweit';
	if( $code == 'KG' ) $country = 'Kirgizi&euml;';
	if( $code == 'LA' ) $country = 'Laos';
	if( $code == 'LV' ) $country = 'Letland';
	if( $code == 'LB' ) $country = 'Libanon';
	if( $code == 'LS' ) $country = 'Lesotho';
	if( $code == 'LR' ) $country = 'Liberi&euml;';
	if( $code == 'LY' ) $country = 'Libi&euml;';
	if( $code == 'LI' ) $country = 'Liechtenstein';
	if( $code == 'LT' ) $country = 'Litouwen';
	if( $code == 'LU' ) $country = 'Luxemburg';
	if( $code == 'MO' ) $country = 'Macao';
	if( $code == 'MK' ) $country = 'Macedoni&euml;';
	if( $code == 'MG' ) $country = 'Madagascar';
	if( $code == 'MW' ) $country = 'Malawi';
	if( $code == 'MY' ) $country = 'Maleisi&euml;';
	if( $code == 'MV' ) $country = 'Maldiven';
	if( $code == 'ML' ) $country = 'Mali';
	if( $code == 'MT' ) $country = 'Malta';
	if( $code == 'MH' ) $country = 'Marshall eilanden';
	if( $code == 'MQ' ) $country = 'Martinique';
	if( $code == 'MR' ) $country = 'Mauritani&euml;';
	if( $code == 'MU' ) $country = 'Mauritius';
	if( $code == 'YT' ) $country = 'Mayotte';
	if( $code == 'MX' ) $country = 'Mexico';
	if( $code == 'FM' ) $country = 'Micronesi&euml;';
	if( $code == 'MD' ) $country = 'Moldavi&euml;';
	if( $code == 'MC' ) $country = 'Monaco';
	if( $code == 'MN' ) $country = 'Mongoli&euml;';
	if( $code == 'ME' ) $country = 'Montenegro';
	if( $code == 'MS' ) $country = 'Montserrat';
	if( $code == 'MA' ) $country = 'Marokko';
	if( $code == 'MZ' ) $country = 'Mozambique';
	if( $code == 'MM' ) $country = 'Myanmar';
	if( $code == 'NA' ) $country = 'Namibi&euml;';
	if( $code == 'NR' ) $country = 'Nauru';
	if( $code == 'NP' ) $country = 'Nepal';
	if( $code == 'AN' ) $country = 'Nederlandse Antillen';
	if( $code == 'NL' ) $country = 'Nederland';
	if( $code == 'NC' ) $country = 'Nieuw Caledoni&euml;';
	if( $code == 'NZ' ) $country = 'Nieuw Zeeland';
	if( $code == 'NI' ) $country = 'Nicaragua';
	if( $code == 'NE' ) $country = 'Niger';
	if( $code == 'NG' ) $country = 'Nigeria';
	if( $code == 'NU' ) $country = 'Niue';
	if( $code == 'NF' ) $country = 'Norfolk eiland';
	if( $code == 'MP' ) $country = 'Noordelijke Marianen';
	if( $code == 'NO' ) $country = 'Noorwegen';
	if( $code == 'OM' ) $country = 'Oman';
	if( $code == 'PK' ) $country = 'Pakistan';
	if( $code == 'PW' ) $country = 'Palau';
	if( $code == 'PS' ) $country = 'Palestijnse gebieden';
	if( $code == 'PA' ) $country = 'Panama';
	if( $code == 'PG' ) $country = 'Papua New Guinea';
	if( $code == 'PY' ) $country = 'Paraguay';
	if( $code == 'PE' ) $country = 'Peru';
	if( $code == 'PH' ) $country = 'Philippijnen';
	if( $code == 'PN' ) $country = 'Pitcairn eilanden';
	if( $code == 'PL' ) $country = 'Polen';
	if( $code == 'PT' ) $country = 'Portugal';
	if( $code == 'PR' ) $country = 'Puerto Rico';
	if( $code == 'QA' ) $country = 'Qatar';
	if( $code == 'RE' ) $country = 'Reunion';
	if( $code == 'RO' ) $country = 'Roemeni&euml;';
	if( $code == 'RU' ) $country = 'Rusland';
	if( $code == 'RW' ) $country = 'Rwanda';
	if( $code == 'BL' ) $country = 'Sint Barthelemy';
	if( $code == 'SH' ) $country = 'Sint Helena';
	if( $code == 'KN' ) $country = 'Sint Kitts en Nevis';
	if( $code == 'LC' ) $country = 'Sint Lucia';
	if( $code == 'MF' ) $country = 'Sint Martin';
	if( $code == 'PM' ) $country = 'Sint Pierre en Miquelon';
	if( $code == 'VC' ) $country = 'Sint Vincent en de Grenadines';
	if( $code == 'WS' ) $country = 'Samoa';
	if( $code == 'SM' ) $country = 'San Marino';
	if( $code == 'ST' ) $country = 'Sao Tome en Principe';
	if( $code == 'SA' ) $country = 'Saudi Arabi&euml;';
	if( $code == 'SN' ) $country = 'Senegal';
	if( $code == 'RS' ) $country = 'Servi&euml;';
	if( $code == 'SC' ) $country = 'Seychelles';
	if( $code == 'SL' ) $country = 'Sierra Leone';
	if( $code == 'SG' ) $country = 'Singapore';
	if( $code == 'SK' ) $country = 'Slowakije';
	if( $code == 'SI' ) $country = 'Sloveni&euml;';
	if( $code == 'SB' ) $country = 'Salomon eilanden';
	if( $code == 'SO' ) $country = 'Somali&euml;';
	if( $code == 'ZA' ) $country = 'Zuid Afrika';
	if( $code == 'GS' ) $country = 'Zuid Georgi&euml; en de Zuid Sandwich eilanden';
	if( $code == 'ES' ) $country = 'Spanje';
	if( $code == 'LK' ) $country = 'Sri Lanka';
	if( $code == 'SD' ) $country = 'Sudan';
	if( $code == 'SR' ) $country = 'Suriname';
	if( $code == 'SJ' ) $country = 'Svalbard en Jan Mayen eilanden';
	if( $code == 'SZ' ) $country = 'Swaziland';
	if( $code == 'SE' ) $country = 'Zweden';
	if( $code == 'CH' ) $country = 'Zwitserland';
	if( $code == 'SY' ) $country = 'Syri&euml;';
	if( $code == 'TW' ) $country = 'Taiwan';
	if( $code == 'TJ' ) $country = 'Tajikistan';
	if( $code == 'TZ' ) $country = 'Tanzania';
	if( $code == 'TH' ) $country = 'Thailand';
	if( $code == 'TL' ) $country = 'Timor-Leste';
	if( $code == 'TG' ) $country = 'Togo';
	if( $code == 'TK' ) $country = 'Tokelau';
	if( $code == 'TO' ) $country = 'Tonga';
	if( $code == 'TT' ) $country = 'Trinidad en Tobago';
	if( $code == 'TN' ) $country = 'Tunesi&euml;';
	if( $code == 'TR' ) $country = 'Turkije';
	if( $code == 'TM' ) $country = 'Turkmenistan';
	if( $code == 'TC' ) $country = 'Turks en Caicos eilanden';
	if( $code == 'TV' ) $country = 'Tuvalu';
	if( $code == 'UG' ) $country = 'Uganda';
	if( $code == 'UA' ) $country = 'Ukra&iuml;ne';
	if( $code == 'AE' ) $country = 'Arabische Emiraten';
	if( $code == 'UK' ) $country = 'Groot Britanni&euml;';
	if( $code == 'US' ) $country = 'Verenigde Staten';
	if( $code == 'UM' ) $country = 'Verenigde Staten Kleine eilanden';
	if( $code == 'VI' ) $country = 'Verenigde Staten Maagden eilanden';
	if( $code == 'UY' ) $country = 'Uruguay';
	if( $code == 'UZ' ) $country = 'Uzbekistan';
	if( $code == 'VU' ) $country = 'Vanuatu';
	if( $code == 'VE' ) $country = 'Venezuela';
	if( $code == 'VN' ) $country = 'Vietnam';
	if( $code == 'WF' ) $country = 'Wallis en Futuna';
	if( $code == 'EH' ) $country = 'West Sahara';
	if( $code == 'YE' ) $country = 'Yemen';
	if( $code == 'ZM' ) $country = 'Zambia';
	if( $code == 'ZW' ) $country = 'Zimbabwe';
	if( $country == '') $country = $code;
	return $country;
}

// ========================== EINDE Algemene functies =========================
?>
