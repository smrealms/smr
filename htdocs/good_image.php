<?php

include('config.inc');
include(ENGINE . 'Old_School/smr.inc');

$good_id = decode_good( $_SERVER['QUERY_STRING'] );

$filename = WWW . 'images/port/$good_id.gif';

if (!file_exists($filename))
	exit;

$handle = fopen($filename, 'r');
$contents = fread($handle, filesize($filename));
fclose($handle);




header('Content-Type: image/gif');
header('Content-Disposition: inline');
echo $contents;

?>