<?php declare(strict_types=1);
try {
	require_once('config.inc');

	$template = new Template();

	// Get a list of all the shops that sell each weapon
	$weaponLocs = [];
	$db = new SmrMySqlDatabase();
	$db->query('SELECT weapon_type_id, location_type.* FROM location_sells_weapons JOIN weapon_type USING (weapon_type_id) JOIN location_type USING (location_type_id) WHERE location_type_id != ' . $db->escapeNumber(RACE_WARS_WEAPONS));
	while ($db->nextRecord()) {
		$weaponLocs[$db->getInt('weapon_type_id')][] = SmrLocation::getLocation($db->getInt('location_type_id'), false, $db)->getName();
	}

	// Get a list of all locations that sell weapons
	$allLocs = array_unique(array_merge(...$weaponLocs));
	sort($allLocs);
	$template->assign('AllLocs', $allLocs);

	// Get all the properties to display for each weapon
	$weapons = [];
	foreach (SmrWeaponType::getAllWeaponTypes() as $weapon) {
		switch ($weapon->getBuyerRestriction()) {
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
			'weapon_name' => $weapon->getName(),
			'race_id' => $weapon->getRaceID(),
			'race_name' => $weapon->getRaceName(),
			'cost' => number_format($weapon->getCost()),
			'shield_damage' => $weapon->getShieldDamage(),
			'armour_damage' => $weapon->getArmourDamage(),
			'accuracy' => $weapon->getAccuracy(),
			'power_level' => $weapon->getPowerLevel(),
			'locs' => $weaponLocs[$weapon->getWeaponTypeID()] ?? [],
		];
	}
	$template->assign('Weapons', $weapons);

	$powerLevels = array_unique(array_column($weapons, 'power_level'));
	rsort($powerLevels);
	$template->assign('PowerLevels', $powerLevels);

	$template->display('weapon_list.php');
} catch (Throwable $e) {
	handleException($e);
}
