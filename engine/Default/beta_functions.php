<?php
if(!ENABLE_BETA) {
	create_error('Beta functions are disabled.');
}

$template->assign('PageTopic', 'Beta Functions');

// container for all links
$container = create_container('beta_func_processing.php', 'beta_functions.php');

// let them map all
$container['func'] = 'Map';
$template->assign('MapHREF', SmrSession::getNewHREF($container));

// let them get money
$container['func'] = 'Money';
$template->assign('MoneyHREF', SmrSession::getNewHREF($container));

//next time for ship
$container['func'] = 'Ship';
$template->assign('ShipHREF', SmrSession::getNewHREF($container));
$shipList = [];
$db->query('SELECT * FROM ship_type ORDER BY ship_name');
while ($db->nextRecord()) {
	$shipList[] = [
		'ID' => $db->getInt('ship_type_id'),
		'Name' => $db->getField('ship_name'),
	];
}
$template->assign('ShipList', $shipList);

//next weapons
$container['func'] = 'Weapon';
$template->assign('AddWeaponHREF', SmrSession::getNewHREF($container));
$weaponList = [];
$db->query('SELECT * FROM weapon_type ORDER BY weapon_name');
while ($db->nextRecord()) {
	$weaponList[] = [
		'ID' => $db->getInt('weapon_type_id'),
		'Name' => $db->getField('weapon_name'),
	];
}
$template->assign('WeaponList', $weaponList);

//Remove Weapons
$container['func'] = 'RemWeapon';
$template->assign('RemoveWeaponsHREF', SmrSession::getNewHREF($container));

//allow to get full hardware
$container['func'] = 'Uno';
$template->assign('UnoHREF', SmrSession::getNewHREF($container));

//move whereever you want
$container['func'] = 'Warp';
$template->assign('WarpHREF', SmrSession::getNewHREF($container));

//set turns
$container['func'] = 'Turns';
$template->assign('TurnsHREF', SmrSession::getNewHREF($container));

//set experience
$container['func'] = 'Exp';
$template->assign('ExperienceHREF', SmrSession::getNewHREF($container));

//Set alignment
$container['func'] = 'Align';
$template->assign('AlignmentHREF', SmrSession::getNewHREF($container));

//add any type of hardware
$container['func'] = 'Hard_add';
$template->assign('HardwareHREF', SmrSession::getNewHREF($container));
$hardware = [];
$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
while ($db->nextRecord()) {
	$hardware[] = [
		'ID' => $db->getInt('hardware_type_id'),
		'Name' => $db->getField('hardware_name'),
	];
}
$template->assign('Hardware', $hardware);

//change personal relations
$container['func'] = 'Relations';
$template->assign('PersonalRelationsHREF', SmrSession::getNewHREF($container));

//change race relations
$container['func'] = 'Race_Relations';
$template->assign('RaceRelationsHREF', SmrSession::getNewHREF($container));

//change race
$container['func'] = 'Race';
$template->assign('ChangeRaceHREF', SmrSession::getNewHREF($container));

if ($sector->hasPlanet()) {
	$container['func'] = 'planet_buildings';
	$template->assign('MaxBuildingsHREF', SmrSession::getNewHREF($container));

	$container['func'] = 'planet_defenses';
	$template->assign('MaxDefensesHREF', SmrSession::getNewHREF($container));

	$container['func'] = 'planet_stockpile';
	$template->assign('MaxStockpileHREF', SmrSession::getNewHREF($container));
}
