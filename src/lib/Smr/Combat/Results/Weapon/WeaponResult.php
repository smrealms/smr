<?php declare(strict_types=1);

namespace Smr\Combat\Results\Weapon;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Weapon\AbstractWeapon;

abstract class WeaponResult {

	protected function __construct(
		public readonly AbstractWeapon $weapon,
		public readonly CombatantInterface $target,
	) {}

}
