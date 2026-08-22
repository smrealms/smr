<?php declare(strict_types=1);

namespace Smr\Combat\Results\Damage;

/**
 * Common outcome of weapon damage applied to any combatant.
 */
abstract class TakenDamage {

	public function __construct(
		public bool $killingShot,
		public bool $targetAlreadyDead,
		public int $combatDroneDamage,
		public int $numCombatDrones,
		public bool $hasCombatDrones,
		public int $totalDamage,
	) {}

}
