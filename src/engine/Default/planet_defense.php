<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

require('planet.inc.php');

$container = Page::create('planet_defense_processing.php');
$container['type_id'] = HARDWARE_SHIELDS;
$template->assign('TransferShieldsHref', $container->href());

$container['type_id'] = HARDWARE_COMBAT;
$template->assign('TransferCDsHref', $container->href());

$container['type_id'] = HARDWARE_ARMOUR;
$template->assign('TransferArmourHref', $container->href());

$container = Page::create('planet_defense_weapon_processing.php');
$template->assign('WeaponProcessingHREF', $container->href());
