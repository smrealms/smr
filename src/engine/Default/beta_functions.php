<?php declare(strict_types=1);
if (!ENABLE_BETA) {
	create_error('Beta functions are disabled.');
}

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Beta Functions');

// container for all links
$container = Page::create('beta_func_processing.php', 'beta_functions.php');

// let them map all
$container['func'] = 'Map';
$template->assign('MapHREF', $container->href());

// let them get money
$container['func'] = 'Money';
$template->assign('MoneyHREF', $container->href());

//next time for ship
$container['func'] = 'Ship';
$template->assign('ShipHREF', $container->href());
$shipList = [];
$db = Smr\Database::getInstance();
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
$template->assign('AddWeaponHREF', $container->href());
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
$template->assign('RemoveWeaponsHREF', $container->href());

//allow to get full hardware
$container['func'] = 'Uno';
$template->assign('UnoHREF', $container->href());

//move whereever you want
$container['func'] = 'Warp';
$template->assign('WarpHREF', $container->href());

//set turns
$container['func'] = 'Turns';
$template->assign('TurnsHREF', $container->href());

//set experience
$container['func'] = 'Exp';
$template->assign('ExperienceHREF', $container->href());

//Set alignment
$container['func'] = 'Align';
$template->assign('AlignmentHREF', $container->href());

//add any type of hardware
$container['func'] = 'Hard_add';
$template->assign('HardwareHREF', $container->href());
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
$template->assign('PersonalRelationsHREF', $container->href());

//change race relations
$container['func'] = 'Race_Relations';
$template->assign('RaceRelationsHREF', $container->href());

//change race
$container['func'] = 'Race';
$template->assign('ChangeRaceHREF', $container->href());

if ($sector->hasPlanet()) {
	$container['func'] = 'planet_buildings';
	$template->assign('MaxBuildingsHREF', $container->href());

	$container['func'] = 'planet_defenses';
	$template->assign('MaxDefensesHREF', $container->href());

	$container['func'] = 'planet_stockpile';
	$template->assign('MaxStockpileHREF', $container->href());
}
