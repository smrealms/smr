<?php declare(strict_types=1);

require('planet.inc');

$container = create_container('planet_defense_processing.php');
$container['type_id'] = 1;
$template->assign('TransferShieldsHref', SmrSession::getNewHREF($container));

$container['type_id'] = 4;
$template->assign('TransferCDsHref', SmrSession::getNewHREF($container));

$container['type_id'] = 2;
$template->assign('TransferArmourHref', SmrSession::getNewHREF($container));

$container = create_container('planet_defense_weapon_processing.php');
$template->assign('WeaponProcessingHREF', SmrSession::getNewHREF($container));
