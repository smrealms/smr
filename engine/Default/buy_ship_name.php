<?php declare(strict_types=1);

$template->assign('PageTopic', 'Naming Your Ship');
$template->assign('ShipNameFormHref', SmrSession::getNewHREF(create_container('buy_ship_name_processing.php')));
