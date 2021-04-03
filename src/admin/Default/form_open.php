<?php declare(strict_types=1);

$template->assign('PageTopic', 'Open/Close Forms');


$container = Page::create('form_open_processing.php');
$container['type'] = 'FEATURE';
$container['is_open'] = Globals::isFeatureRequestOpen();
$template->assign('ToggleHREF', $container->href());

$template->assign('Color', Globals::isFeatureRequestOpen() ? 'green' : 'red');
$template->assign('Status', Globals::isFeatureRequestOpen() ? 'OPEN' : 'CLOSED');
$template->assign('Action', Globals::isFeatureRequestOpen() ? 'Close' : 'Open');
