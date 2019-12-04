<?php declare(strict_types=1);

$template->assign('PageTopic', 'Weapon Reorder');

if (isset($var['Up']) && is_numeric($var['Up'])) {
	$ship->moveWeaponUp($var['Up']);
}

if (isset($var['Down']) && is_numeric($var['Down'])) {
	$ship->moveWeaponDown($var['Down']);
}

if (isset($var['Form'])) {
	if (isset($_REQUEST['weapon_reorder']) && is_array($_REQUEST['weapon_reorder']))
		$ship->setWeaponLocations($_REQUEST['weapon_reorder']);
}
