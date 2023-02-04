<?php declare(strict_types=1);

use Smr\Combat\Weapon\Weapon;
use Smr\Database;
use Smr\Location;
use Smr\Template;
use Smr\WeaponType;

try {
	require_once('../bootstrap.php');

	$template = Template::getInstance();

	// Get a list of all the shops that sell each weapon
	$weaponLocs = [];
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT weapon_type_id, location_type.* FROM location_sells_weapons JOIN weapon_type USING (weapon_type_id) JOIN location_type USING (location_type_id) WHERE location_type_id != ' . $db->escapeNumber(RACE_WARS_WEAPONS));
	foreach ($dbResult->records() as $dbRecord) {
		$gameID = 0; // doesn't matter for weapon list (yet)
		$weaponLocs[$dbRecord->getInt('weapon_type_id')][] = Location::getLocation($gameID, $dbRecord->getInt('location_type_id'), false, $dbRecord)->getName();
	}

	// Get a list of all locations that sell weapons
	$allLocs = array_unique(array_merge(...$weaponLocs));
	sort($allLocs);
	$template->assign('AllLocs', $allLocs);

	// Get all the properties to display for each weapon
	$weapons = [];
	foreach (WeaponType::getAllWeaponTypes() as $weapon) {
		$restrictions = [$weapon->getBuyerRestriction()->display()];
		if (Weapon::getWeapon($weapon->getWeaponTypeID())->isUniqueType()) {
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
