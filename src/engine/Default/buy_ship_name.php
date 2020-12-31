<?php declare(strict_types=1);

$costs = Globals::getBuyShipNameCosts();

$container = create_container('buy_ship_name_processing.php');
$container['costs'] = $costs;

$template->assign('PageTopic', 'Naming Your Ship');
$template->assign('Costs', $costs);
$template->assign('ShipNameFormHref', SmrSession::getNewHREF($container));
