<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$template->assign('PageTopic', 'Naming Your Ship');

$container = Page::create('buy_ship_name_preview_processing.php');
$container->addVar('ShipName');
$container->addVar('cost');
$template->assign('ContinueHREF', $container->href());

$template->assign('ShipName', $var['ShipName']);
