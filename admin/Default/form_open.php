<?php

$template->assign('PageTopic','Open/Close Forms');


$container = create_container('form_open_processing.php');
$container['type'] = 'FEATURE';
$container['open'] = Globals::isFeatureRequestOpen();
$PHP_OUTPUT.='<p>Feature Request Status: <span class="';
if(Globals::isFeatureRequestOpen())
	$PHP_OUTPUT.= 'green">OPEN';
else
	$PHP_OUTPUT.= 'red">CLOSED';
$PHP_OUTPUT.= '</span></p>';
$PHP_OUTPUT.=create_link($container, '<b>'.(Globals::isFeatureRequestOpen() ? 'Close' : 'Open') . ' Application Form</b>');
