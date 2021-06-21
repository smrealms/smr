<?php declare(strict_types=1);
if (!ENABLE_BETA) {
	create_error('Beta functions are disabled.');
}

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$sector = $session->getPlayer()->getSector();

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
foreach (SmrShipType::getAll() as $shipTypeID => $shipType) {
	$shipList[$shipTypeID] = $shipType->getName();
}
asort($shipList); // sort by name
$template->assign('ShipList', $shipList);

//next weapons
$container['func'] = 'Weapon';
$template->assign('AddWeaponHREF', $container->href());
$weaponList = [];
foreach (SmrWeaponType::getAllWeaponTypes() as $weaponTypeID => $weaponType) {
	$weaponList[$weaponTypeID] = $weaponType->getName();
}
asort($weaponList); // sort by name
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
foreach (Globals::getHardwareTypes() as $hardwareTypeID => $hardwareType) {
	$hardware[$hardwareTypeID] = $hardwareType['Name'];
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
