<?php declare(strict_types=1);

$template->assign('PageTopic', 'Naming Your Ship');

$container = create_container('buy_ship_name_preview_processing.php');
transfer('ShipName');
$template->assign('ContinueHREF', SmrSession::getNewHREF($container));

$template->assign('ShipName', $var['ShipName']);
