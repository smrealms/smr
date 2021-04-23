<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$ship = $session->getPlayer()->getShip();

if (isset($var['Up']) && is_numeric($var['Up'])) {
	$ship->moveWeaponUp($var['Up']);
}

if (isset($var['Down']) && is_numeric($var['Down'])) {
	$ship->moveWeaponDown($var['Down']);
}

if (isset($var['Form'])) {
	$ship->setWeaponLocations(Request::getIntArray('weapon_reorder'));
}

Page::create('skeleton.php', 'weapon_reorder.php')->go();
