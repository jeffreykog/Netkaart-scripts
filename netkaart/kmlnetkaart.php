<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script generates the main KML    */
/* Which contains credits, legend,       */
/* copyright stuff etc.                  */
/* ===================================== */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');

// =========================== Access logging ===========================
$ref = @$_SERVER['HTTP_REFERER'];
$agent = @$_SERVER['HTTP_USER_AGENT'];
$ip = @$_SERVER['REMOTE_ADDR'];
$remdom = gethostbyaddr($ip);
$country = file_get_contents("http://ipinfo.io/{$ip}/country");
$tracking_page_name = __FILE__;
$method = $scriptnaam .' '. $scriptversie;
// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die(LogMySQLError(mysqli_connect_error(), basename(__FILE__), 'Not connected to MySQL'));}

$strSQL = "INSERT INTO track(ref, agent, ip, domain, tracking_page_name, cntry, method) VALUES ('$ref','$agent','$ip','$remdom','$tracking_page_name','$country','$method')";
$test=mysqli_query($connection, $strSQL);
//close connection
mysqli_close($connection);
// ======================== EINDE Access Logging =========================

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">';
$kml[] = '<Document>';
$kml[] = '<Folder>';

// =========================== Beschrijving ==============================
$kml[] = '<name>GE-Netkaart van de Benelux</name>';
$kml[] = '	<description><![CDATA[<i><b>'.$KaartVersie.'</b> - '.$KaartDatum.'</i><br>';
$kml[] = '	<b>www.hoogspanningsnet.com</b><br>';
$kml[] = '	<p><img src="'. $WebServer . $UseDir . $IconDir .'emblem_n_pub.png"/></p>';
$kml[] = '	<p>'.$KaartVersie.'  "<i>'.$KaartNaam.'</i>"</p>';
$kml[] = '	'.$KaartCredits.']]></description>';
// ======================== EINDE Beschrijving ============================

// ================================ Legenda ===================================
$kml[] = '<ScreenOverlay id="legend">';
$kml[] = '	<name>Legenda</name>';
$kml[] = '	<open>1</open>';
$kml[] = '	<Icon>';
$kml[] = '		<href>'. $WebServer . $UseDir . $IconDir .'Legenda_SNK4.png</href>';
$kml[] = '	</Icon>';
$kml[] = '	<overlayXY x="0" y="0.1" xunits="fraction" yunits="fraction"/>';
$kml[] = '	<screenXY x="0" y="0.3" xunits="fraction" yunits="fraction"/>';
$kml[] = '	<rotationXY x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>';
$kml[] = '	<size x="0" y="375" xunits="pixels" yunits="pixels"/>';
$kml[] = '</ScreenOverlay>';
// ============================ Einde Legenda =================================

// ============================== Kaart weergeven ============================
$kml[] = ' 	<NetworkLink>';
$kml[] = ' 		<name>Landen</name>';
$kml[] = ' 		<open>1</open>';
$kml[] = ' 		<Link>';
$kml[] = ' 			<href>'. $WebServer . $UseDir .'genereerkaart.php</href>';
$kml[] = ' 	    		<viewRefreshMode>onStop</viewRefreshMode>';
$kml[] = ' 	    		<viewRefreshTime>0.5</viewRefreshTime>';
$kml[] = ' 	    		<viewFormat>BBOX=[bboxWest],[bboxSouth],[bboxEast],[bboxNorth];CAMERA=[cameraAlt]</viewFormat>';
$kml[] = ' 	  	</Link>';
$kml[] = ' 	 </NetworkLink>';
// ============================= kaart weergeven =============================

// ============================== Over de kaart  ==============================
$kml[] = ' 	<Folder>';
$kml[] = ' 		<name>Over de Netkaart</name>';
$kml[] = ' 		<Style>';
$kml[] = ' 			<ListStyle>';
$kml[] = ' 				<listItemType>check</listItemType>';
$kml[] = ' 				<bgColor>00ffffff</bgColor>';
$kml[] = ' 				<maxSnippetLines>2</maxSnippetLines>';
$kml[] = ' 			</ListStyle>';
$kml[] = ' 		</Style>';
$kml[] = '           <Folder><name>Settings</name>';
$kml[] = ' 			<open>1</open>';
$kml[] = ' 			<Snippet maxLines="0"></Snippet>';
$kml[] = '			<description>';
$kml[] = '				<![CDATA[<iframe width="640" height="380" frameborder="0" src="'.$WebServer.$UseDir.'usersettings.php"></iframe>]]>';
$kml[] = '			</description>';
$kml[] = ' 		</Folder>';
$kml[] = ' 		<Folder>';
$kml[] = ' 			<name>&lt;a href=&quot;http://www.hoogspanningsnet.com/google-earth/standaardnetkaart/gebruiksaanwijzing/&quot;&gt;Online gebruiksaanwijzing&lt;/a&gt;</name>';
$kml[] = ' 			<open>1</open>';
$kml[] = ' 			<Style>';
$kml[] = ' 				<ListStyle>';
$kml[] = ' 					<listItemType>check</listItemType>';
$kml[] = ' 					<bgColor>00ffffff</bgColor>';
$kml[] = ' 					<maxSnippetLines>2</maxSnippetLines>';
$kml[] = ' 				</ListStyle>';
$kml[] = ' 			</Style>';
$kml[] = ' 		</Folder>';
$kml[] = ' 		<Folder>';
$kml[] = ' 			<name>&lt;a href=&quot;http://www.hoogspanningsforum.com/viewforum.php?f=13&quot;&gt;Bug? Update? Meld hem ons&lt;/a&gt;</name>';
$kml[] = ' 			<open>1</open>';
$kml[] = ' 			<Style>';
$kml[] = ' 				<ListStyle>';
$kml[] = ' 					<listItemType>checkHideChildren</listItemType>';
$kml[] = ' 					<bgColor>00ffffff</bgColor>';
$kml[] = ' 					<maxSnippetLines>2</maxSnippetLines>';
$kml[] = ' 				</ListStyle>';
$kml[] = ' 			</Style>';
$kml[] = ' 		</Folder>';
$kml[] = ' 		<Folder>';
$kml[] = ' 			<name>&lt;a href=&quot;http://www.hoogspanningsnet.com/google-earth/standaardnetkaart/release-notes/&quot;&gt;Release notes&lt;/a&gt;</name>';
$kml[] = ' 			<open>1</open>';
$kml[] = ' 		</Folder>';
$kml[] = ' 		<Folder>';
$kml[] = ' 			<name>&lt;a href=&quot;http://creativecommons.org/licenses/by-nc-nd/3.0/&quot;&gt;Copyrights&lt;/a&gt;</name>';
$kml[] = ' 			<open>1</open>';
$kml[] = ' 		</Folder>';
$kml[] = ' 		<Folder>';
$kml[] = ' 			<name>&lt;a href=&quot;http://www.hoogspanningsnet.com/google-earth/disclaimer/&quot;&gt;Disclaimer&lt;/a&gt;</name>';
$kml[] = ' 			<Folder>';
$kml[] = ' 				<visibility>0</visibility>';
$kml[] = ' 				<name>icon</name>';
$kml[] = ' 				<ScreenOverlay id="legend">';
$kml[] = ' 					<name>Emblem</name>';
$kml[] = ' 					<Icon>';
$kml[] = ' 						<href>'. $WebServer . $UseDir . $IconDir .'hoogspanningsnet.com-emblem.png</href>';
$kml[] = ' 					</Icon>';
$kml[] = ' 					<overlayXY x="0" y="0.1" xunits="fraction" yunits="fraction"/>';
$kml[] = ' 					<screenXY x="0.018" y="0.1" xunits="fraction" yunits="fraction"/>';
$kml[] = ' 					<rotationXY x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>';
$kml[] = ' 					<size x="0" y="52.5" xunits="pixels" yunits="pixels"/>';
$kml[] = ' 				</ScreenOverlay>';
$kml[] = ' 			</Folder>';
$kml[] = ' 		</Folder>';
$kml[] = ' 	</Folder>';
// =========================== EINDE Over de kaart ===========================

// End XML file
$kml[] = ' </Folder>';
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);

header('Content-type: application/vnd.google-earth.kml+xml');
header('Content-Disposition: attachment; filename="HoogspanningsNet Netkaart.kml"');
echo $kmlOutput;
?>