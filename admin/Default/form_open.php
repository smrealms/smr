<?php

$template->assign('PageTopic','Open/Close Forms');


$container = array();
$container['url'] = 'form_open_processing.php';
$container['type'] = 'BETA';
$container['open'] = Globals::isBetaOpen();
$PHP_OUTPUT.='<p>Beta Application Status: <span style="color:';
if(Globals::isBetaOpen())
	$PHP_OUTPUT.= 'green;">OPEN';
else
	$PHP_OUTPUT.= 'red;">CLOSED';
$PHP_OUTPUT.= '</span></p>';
$PHP_OUTPUT.=create_link($container, '<b>'.(Globals::isBetaOpen() ? 'Close' : 'Open') . ' Application Form</b>');


$container['type'] = 'FEATURE';
$container['open'] = Globals::isFeatureRequestOpen();
$PHP_OUTPUT.='<p>Feature Request Status: <span style="color:';
if(Globals::isFeatureRequestOpen())
	$PHP_OUTPUT.= 'green;">OPEN';
else
	$PHP_OUTPUT.= 'red;">CLOSED';
$PHP_OUTPUT.= '</span></p>';
$PHP_OUTPUT.=create_link($container, '<b>'.(Globals::isFeatureRequestOpen() ? 'Close' : 'Open') . ' Application Form</b>');

?>