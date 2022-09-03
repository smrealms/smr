<?php declare(strict_types=1);

use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$ship = $session->getPlayer()->getShip();

if (isset($var['Up'])) {
	$ship->moveWeaponUp($var['Up']);
}

if (isset($var['Down'])) {
	$ship->moveWeaponDown($var['Down']);
}

if (isset($var['Form'])) {
	$ship->setWeaponLocations(Request::getIntArray('weapon_reorder'));
}

Page::create('weapon_reorder.php')->go();
