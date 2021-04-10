<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', $location->getName());
$template->assign('ThisLocation', $location);

$weaponsSold = $location->getWeaponsSold();

// Check if any enhanced weapons are available
$events = SmrEnhancedWeaponEvent::getShopEvents($player->getGameID(), $player->getSectorID(), $location->getTypeID());
foreach ($events as $event) {
	$weapon = $event->getWeapon();
	$weaponsSold[$weapon->getWeaponTypeID()] = $weapon;
}

$template->assign('WeaponsSold', $weaponsSold);
