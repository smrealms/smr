<?php declare(strict_types=1);

if (isset($var['Up']) && is_numeric($var['Up'])) {
	$ship->moveWeaponUp($var['Up']);
}

if (isset($var['Down']) && is_numeric($var['Down'])) {
	$ship->moveWeaponDown($var['Down']);
}

if (isset($var['Form'])) {
	$ship->setWeaponLocations(Request::getIntArray('weapon_reorder'));
}

forward(create_container('skeleton.php', 'weapon_reorder.php'));
