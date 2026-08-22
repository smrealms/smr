<?php declare(strict_types=1);

namespace Smr\Combat\Results\Weapon;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Weapon\AbstractWeapon;

/**
 * Represents a weapon that missed a target.
 */
final class MissedWeaponResult extends WeaponResult {

	public function __construct(
		AbstractWeapon $weapon,
		CombatantInterface $target,
	) {
		parent::__construct($weapon, $target);
	}

}
