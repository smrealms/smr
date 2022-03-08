<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$template = Smr\Template::getInstance();

	// Get a list of all the shops that sell each weapon
	$weaponLocs = [];
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT weapon_type_id, location_type.* FROM location_sells_weapons JOIN weapon_type USING (weapon_type_id) JOIN location_type USING (location_type_id) WHERE location_type_id != ' . $db->escapeNumber(RACE_WARS_WEAPONS));
	foreach ($dbResult->records() as $dbRecord) {
		$weaponLocs[$dbRecord->getInt('weapon_type_id')][] = SmrLocation::getLocation($dbRecord->getInt('location_type_id'), false, $dbRecord)->getName();
	}

	// Get a list of all locations that sell weapons
	$allLocs = array_unique(array_merge(...$weaponLocs));
	sort($allLocs);
	$template->assign('AllLocs', $allLocs);

	// Get all the properties to display for each weapon
	$weapons = [];
	foreach (SmrWeaponType::getAllWeaponTypes() as $weapon) {
		$restrictions = match ($weapon->getBuyerRestriction()) {
			BUYER_RESTRICTION_NONE => [],
			BUYER_RESTRICTION_GOOD => ['<div class="dgreen">Good</div>'],
			BUYER_RESTRICTION_EVIL => ['<div class="red">Evil</div>'],
			BUYER_RESTRICTION_NEWBIE => ['<div style="color: #06F;">Newbie</div>'],
			BUYER_RESTRICTION_PORT => ['<div class="yellow">Port</div>'],
			BUYER_RESTRICTION_PLANET => ['<div class="yellow">Planet</div>'],
		};
		if (SmrWeapon::getWeapon($weapon->getWeaponTypeID())->isUniqueType()) {
			$restrictions[] = '<div style="color: #64B9B9">Unique</div>';
		}
		$weapons[] = [
			'restriction' => $restrictions,
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
