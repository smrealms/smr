<?php declare(strict_types=1);
try {
	require_once('config.inc');
	
	$template = new Template();

	$db = new SmrMySqlDatabase();
	$template->assign('power', buildSelector($db, "power_level", "weapon_type"));
	$template->assign('raceBoxes', buildRaceBox($db));

	$weapons = [];
	$db->query('SELECT * FROM weapon_type JOIN race USING(race_id)');
	while ($db->nextRecord()) {
		switch ($db->getInt('buyer_restriction')) {
			case BUYER_RESTRICTION_GOOD:
				$restriction = '<span class="dgreen">Good</span>';
			break;
			case BUYER_RESTRICTION_EVIL:
				$restriction = '<span class="red">Evil</span>';
			break;
			case BUYER_RESTRICTION_NEWBIE:
				$restriction = '<span style="color: #06F;">Newbie</span>';
			break;
			case BUYER_RESTRICTION_PORT:
				$restriction = '<span class="yellow">Port</span>';
			break;
			case BUYER_RESTRICTION_PLANET:
				$restriction = '<span class="yellow">Planet</span>';
			break;
			default:
				$restriction = '';
		}
		$weapons[] = [
			'restriction' => $restriction,
			'weapon_name' => $db->getField('weapon_name'),
			'race_id' => $db->getInt('race_id'),
			'race_name' => $db->getField('race_name'),
			'cost' => number_format($db->getInt('cost')),
			'shield_damage' => $db->getInt('shield_damage'),
			'armour_damage' => $db->getInt('armour_damage'),
			'accuracy' => $db->getInt('accuracy'),
			'power_level' => $db->getInt('power_level'),
		];
	}
	$template->assign('Weapons', $weapons);

	$template->display('weapon_list.php');
} catch (Throwable $e) {
	handleException($e);
}

function buildSelector($db, $name, $table) {
	$selector = '<select onchange="filterSelect(this)"><option>All</option>';
	$db->query("SELECT DISTINCT " . $name . " FROM " . $table . " ORDER BY " . $name);
	while ($db->nextRecord()) {
		$selector .= '<option>' . $db->getField($name) . '</option>';
	}
	$selector .= '</select>';
	return $selector;
}

function buildRaceBox($db) {
	$racebox = '<form id="raceform" name="raceform" style="text-align:center;">';
	$db->query('SELECT race_id, race_name FROM race ORDER BY race_name');
	foreach (Globals::getRaces() as $raceID => $raceData) {
		$raceName = $raceData['Race Name'];
		$racebox .= '
			<input type="checkbox" id="race'.$raceID . '" name="races" value="' . $raceName . '" onClick="raceToggle()">
			<label for="race'.$raceID . '" class="race' . $raceID . '">' . $raceName . '</label>&thinsp;';
	}
	$racebox .= '</form>';
	return $racebox;
}
