<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$costs = Globals::getBuyShipNameCosts();

$container = Page::create('buy_ship_name_processing.php');
$container['costs'] = $costs;

$template->assign('PageTopic', 'Naming Your Ship');
$template->assign('Costs', $costs);
$template->assign('ShipNameFormHref', $container->href());
