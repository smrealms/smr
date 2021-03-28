<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$template = Smr\Template::getInstance();

	$shipArray = [];
	foreach (SmrShipType::getAll() as $shipType) {
		$shipArray[] = buildShipStats($shipType);
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

function buildShipStats($ship) {
	//we want to put them all in an array so we dont have to have 15 td rows
	$restriction = match($ship->getRestriction()) {
		BUYER_RESTRICTION_NONE => '',
		BUYER_RESTRICTION_GOOD => '<span class="dgreen">Good</span>',
		BUYER_RESTRICTION_EVIL => '<span class="red">Evil</span>',
		BUYER_RESTRICTION_NEWBIE => '<span style="color: #06F;">Newbie</span>',
	};

	// Array key is the td class (sort key), and array value is the data value
	$stat = [
		'name' => $ship->getName(),
		'race race' . $ship->getRaceID() => Globals::getRaceName($ship->getRaceID()),
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
	];
	return $stat;
}
