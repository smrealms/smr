<?php declare(strict_types=1);

namespace Smr\Combat\Results\Damage;

/**
 * Raw damage produced by a weapon before calculating actual damage done to a target.
 */
class WeaponDamage {

	public function __construct(
		public int $shieldDamage,
		public int $armourDamage,
		public bool $damageRollover,
		public int $launched = 0,
		public int $kamikaze = 0,
	) {}

}
