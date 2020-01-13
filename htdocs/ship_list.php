<?php declare(strict_types=1);
try {
	require_once('config.inc');

	$template = new Template();

	$db = new SmrMySqlDatabase();
	$template->assign('speed', buildSelector($db, 'speed', 'ship_type'));
	$template->assign('hardpoint', buildSelector($db, 'hardpoint', 'ship_type'));
	$template->assign('toggle', buildToggle());

	$gameType = ''; // no game type here
	foreach (SmrShip::getAllBaseShips($gameType) as $ship) {
		$shipArray[] = buildShipStats($ship);
	}
	$template->assign('shipArray', $shipArray);

	$template->display('ship_list.php');
} catch (Throwable $e) {
	handleException($e);
}

function buildSelector($db, $name, $table) {
	$selector = '<select onchange="filterSelect(this)"><option>All</option>';
	$db->query('SELECT DISTINCT ' . $name . ' FROM ' . $table . ' ORDER BY ' . $name);
	while ($db->nextRecord()) {
		$selector .= '<option>' . $db->getField($name) . '</option>';
	}
	$selector .= '</select>';
	return $selector;
}

function buildToggle() {
	$toggle = '<select onchange="filterSelect(this)">'
	.'<option>All</option>'
	.'<option>Yes</option>'
	.'<option value="">No</option></select>';
	return $toggle;
}

function buildShipStats($ship) {
	//we want to put them all in an array so we dont have to have 15 td rows
	if ($ship['AlignRestriction'] == BUYER_RESTRICTION_GOOD) {
		$restriction = '<span class="dgreen">Good</span>';
	} elseif ($ship['AlignRestriction'] == BUYER_RESTRICTION_EVIL) {
		$restriction = '<span class="red">Evil</span>';
	} else {
		$restriction = '';
	}
	// Array key is the td class (sort key), and array value is the data value
	$stat = [
		'name' => $ship['Name'],
		'race race' . $ship['RaceID'] => Globals::getRaceName($ship['RaceID']),
		'class_' => Globals::getShipClass($ship['ShipClassID']),
		'cost' => number_format($ship['Cost']),
		'speed' => $ship['Speed'],
		'hardpoint' => $ship['Hardpoint'],
		'restriction' => $restriction,
		'shields' => $ship['MaxHardware'][HARDWARE_SHIELDS],
		'armour' => $ship['MaxHardware'][HARDWARE_ARMOUR],
		'cargo' => $ship['MaxHardware'][HARDWARE_CARGO],
		'cds' => $ship['MaxHardware'][HARDWARE_COMBAT],
		'scouts' => $ship['MaxHardware'][HARDWARE_SCOUT],
		'mines' => $ship['MaxHardware'][HARDWARE_MINE],
		'scanner' => $ship['MaxHardware'][HARDWARE_SCANNER] == 1 ? 'Yes' : '',
		'cloak' => $ship['MaxHardware'][HARDWARE_CLOAK] == 1 ? 'Yes' : '',
		'illusion' => $ship['MaxHardware'][HARDWARE_ILLUSION] == 1 ? 'Yes' : '',
		'jump' => $ship['MaxHardware'][HARDWARE_JUMP] == 1 ? 'Yes' : '',
		'scrambler' => $ship['MaxHardware'][HARDWARE_DCS] == 1 ? 'Yes' : '',
	];
	return $stat;
}
