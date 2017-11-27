<?php

/** define the directory **/
$dir = "../temp/";

/*** cycle through all files in the directory ***/
foreach (glob($dir."*") as $file) {

/*** if file is 24 hours (86400 seconds) old then delete it ***/
if (filemtime($file) < time() - 60) {
    unlink($file);
    }
}

?>