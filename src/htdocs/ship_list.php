<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$template = Smr\Template::getInstance();

	// Get a list of all the shops that sell each ship
	$shipLocs = [];
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT ship_type_id, location_type.* FROM location_sells_ships JOIN ship_type USING (ship_type_id) JOIN location_type USING (location_type_id) WHERE location_type_id NOT IN (' . $db->escapeArray([RACE_WARS_SHIPS, LOCATION_TYPE_TEST_SHIPYARD]) . ')');
	foreach ($dbResult->records() as $dbRecord) {
		$shipTypeID = $dbRecord->getInt('ship_type_id');
		$locTypeID = $dbRecord->getInt('location_type_id');
		$shipLocs[$shipTypeID][] = SmrLocation::getLocation($locTypeID, false, $dbRecord)->getName();
	}

	// Get a list of all locations that sell ships
	$allLocs = array_unique(array_merge(...$shipLocs));
	sort($allLocs);
	$template->assign('AllLocs', $allLocs);

	$shipArray = [];
	foreach (SmrShipType::getAll() as $shipType) {
		$shipArray[] = buildShipStats($shipType, $shipLocs[$shipType->getTypeID()] ?? []);
	}
	$template->assign('shipArray', $shipArray);

	$speeds = array_unique(array_column($shipArray, 'speed'));
	rsort($speeds);
	$template->assign('Speeds', $speeds);

	$hardpoints = array_unique(array_column($shipArray, 'hardpoint'));
	rsort($hardpoints);
	$template->assign('Hardpoints', $hardpoints);

	$booleanFields = ['Scanner', 'Cloak', 'Illusion', 'Jump', 'Scrambler'];
	$template->assign('BooleanFields', $booleanFields);

	$template->display('ship_list.php');
} catch (Throwable $e) {
	handleException($e);
}

function buildShipStats(SmrShipType $ship, array $shipLocs): array {
	//we want to put them all in an array so we dont have to have 15 td rows
	$restriction = match ($ship->getRestriction()) {
		BUYER_RESTRICTION_NONE => '',
		BUYER_RESTRICTION_GOOD => '<span class="dgreen">Good</span>',
		BUYER_RESTRICTION_EVIL => '<span class="red">Evil</span>',
		BUYER_RESTRICTION_NEWBIE => '<span style="color: #06F;">Newbie</span>',
	};

	// Array key is the td class (sort key), and array value is the data value
	$stat = [
		'name' => $ship->getName(),
		'race race' . $ship->getRaceID() => Smr\Race::getName($ship->getRaceID()),
		'class_' => Smr\ShipClass::getName($ship->getClassID()),
		'cost' => number_format($ship->getCost()),
		'speed' => $ship->getSpeed(),
		'hardpoint' => $ship->getHardpoints(),
		'restriction' => $restriction,
		'shields' => $ship->getMaxHardware(HARDWARE_SHIELDS),
		'armour' => $ship->getMaxHardware(HARDWARE_ARMOUR),
		'cargo' => $ship->getMaxHardware(HARDWARE_CARGO),
		'cds' => $ship->getMaxHardware(HARDWARE_COMBAT),
		'scouts' => $ship->getMaxHardware(HARDWARE_SCOUT),
		'mines' => $ship->getMaxHardware(HARDWARE_MINE),
		'scanner' => $ship->canHaveScanner() ? 'Yes' : '',
		'cloak' => $ship->canHaveCloak() ? 'Yes' : '',
		'illusion' => $ship->canHaveIllusion() ? 'Yes' : '',
		'jump' => $ship->canHaveJump() ? 'Yes' : '',
		'scrambler' => $ship->canHaveDCS() ? 'Yes' : '',
		'locs' => implode('', array_map(fn(string $name): string => '<div>' . $name . '</div>', $shipLocs)),
	];
	return $stat;
}
