<?php
/* ===================================== */
/* Copyright 2016, Hoogspanningsnet.com  */
/* Script by BaDu                        */
/*                                       */
/* This script checks the feedback for   */
/* validity. Uses session set in         */
/* camimp.php					         */
/* ===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('phpsqlajax_dbinfo.php');
require('hulpfuncties.php');

require_once('plugins/php-github/client/GitHubClient.php');

// Opens a connection to a MySQL server.
$connection = mysqli_connect ($server, $username, $password, $database);
if (!$connection) {	die('Not connected to MySQL'); }

if(isset($_COOKIE['netkaart-language-selected'])) {
	$translate = new Translator($_COOKIE['netkaart-language-selected']);
} else {
	$translate = new Translator('nl');
}

$itemid='';
$itemnm='';
$itemml='';
$itemon='';
$itemtx='';
$itemgt='';

if (isset($_GET['ID'])) {$itemid=$_GET['ID'];}
if (isset($_GET['nm'])) {$itemnm=$_GET['nm'];}
if (isset($_GET['ml'])) {$itemml=$_GET['ml'];}
if (isset($_GET['on'])) {$itemon=$_GET['on'];}
if (isset($_GET['tx'])) {$itemtx=$_GET['tx'];}
if (isset($_GET['gt'])) {$itemgt=$_GET['gt'];}

function ValidID ($id) {
	global $connection;

	$editTP = $id[0];
	$editID = substr($id,1);
	if ($editTP == 'v') { $table = 'Verbindingen';}
	elseif ($editTP == 's') { $table = 'Stationsiconen';}
	elseif ($editTP == 'm') { $table = 'Masten';}
	elseif ($editTP == 't') { $table = 'Stations';}
	elseif ($editTP == 'k') { $table = 'Knooppunten';}
	elseif ($editTP == 'n') { $table = 'Netopeningen';}
	elseif ($editTP == 'b') { $table = 'Bedrijfsmiddelen';}
	else {
		return false;
	}
	$query = 'SELECT ID FROM '.$table. ' WHERE ID='.$editID.' LIMIT 0,1';
	$gegevens = mysqli_query($connection, $query);
	if (!$gegevens) { 
		die('Invalid query'); 
		return false;
	}
	$rij = mysqli_fetch_assoc($gegevens);
	if (!$rij) {
		return false;
	} else {
		return true;
	}
}

function getProtectedValue($obj,$name) {
	$array = (array)$obj;
	$prefix = chr(0).'*'.chr(0);
	return $array[$prefix.$name];
}

session_start();

if (strlen($itemid)>1) {
	$validis = ValidID($itemid);
} else {
	$validis = true;
}

if 	(
	$validis AND
	strlen($itemnm)>2 AND
	strlen($itemon)>10 AND
	strlen($itemtx)>10 AND
	filter_var($itemml, FILTER_VALIDATE_EMAIL) AND
	strlen($itemgt)==4 AND
	$itemgt==$_SESSION["code"]
	) 
{
	$owner = 'Hoogspanningsnet';
	$repo = 'Netkaart';
	$title = $itemon;	
	$body = 'Naam : '.$itemnm.'<br>'.
			'Email : '.$itemml.'<br>'.
			'ID : <a href="https://netkaart.hoogspanningsnet.com/balloon3.php?ID='.$itemid.'">'.$itemid.'</a><br><br>'.
			'Feedback tekst : <br>'.nl2br($itemtx);

	$client = new GitHubClient();
	$client->setCredentials($githubusername, $githubpassword);
	$result = $client->issues->createAnIssue($owner, $repo, $title, $body);
	echo vertaal('Bedankt voor uw feedback we gaan er zo snel mogelijk mee aan de slag').'.<p>';
	echo vertaal('U kunt de voortgang').' <a target="_blank" href="'.getProtectedValue($result,'html_url').'">'.vertaal('hier').'</a> '.vertaal('volgen').'.';
} else {
	echo 'HACKING NOT ALLOWED!!!';
}
$_SESSION["code"]=NULL;
mysqli_close($connection);
?>