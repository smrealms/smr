<?php declare(strict_types=1);
try {
	require_once('config.inc');

	$template = new Template();

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
			'race_name' => Globals::getRaceName($weapon->getRaceID()),
			'cost' => number_format($weapon->getCost()),
			'shield_damage' => $weapon->getShieldDamage(),
			'armour_damage' => $weapon->getArmourDamage(),
			'accuracy' => $weapon->getAccuracy(),
			'power_level' => $weapon->getPowerLevel(),
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
