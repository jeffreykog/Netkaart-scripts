<?php
require('../phpsqlajax_dbinfo.php');

// Check if were logged in, if not goto the start page
if (CheckLoggedIn(TRUE)==false) {header("location:index.php");}

ini_set('memory_limit', '512M');

ob_start();

try {
	// Undefined | Multiple Files | $_FILES Corruption Attack
	// If this request falls under any of them, treat it invalid.
	if (
			!isset($_FILES['file']['error']) ||
			is_array($_FILES['file']['error'])
	) {
		throw new RuntimeException('Invalid parameters.<br>');
	}

	// Check $_FILES['file']['error'] value.
	switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new RuntimeException('No file sent.<br>');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new RuntimeException('Exceeded filesize limit.<br>');
		default:
			throw new RuntimeException('Unknown errors.<br>');
	}

	// You should also check filesize here.
	if ($_FILES['file']['size'] > 50000000) {
		throw new RuntimeException('Exceeded filesize limit.<br>');
	}

	// You should name it uniquely.
	// DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
	// On this example, obtain safe unique name from its binary data.
	if (!move_uploaded_file( $_FILES['file']['tmp_name'], 'upload/doc.kml'))
	{
		throw new RuntimeException('Failed to move uploaded file.<br>');
	}
	chmod('upload/doc.kml', 0777);

	echo 'File is uploaded successfully.<br>';

	require('importxml.php');
	
} catch (RuntimeException $e) {
	echo $e->getMessage();
}
?>