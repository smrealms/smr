<?php declare(strict_types=1);

require('planet.inc.php');

$container = Page::create('planet_defense_processing.php');
$container['type_id'] = 1;
$template->assign('TransferShieldsHref', $container->href());

$container['type_id'] = 4;
$template->assign('TransferCDsHref', $container->href());

$container['type_id'] = 2;
$template->assign('TransferArmourHref', $container->href());

$container = Page::create('planet_defense_weapon_processing.php');
$template->assign('WeaponProcessingHREF', $container->href());
