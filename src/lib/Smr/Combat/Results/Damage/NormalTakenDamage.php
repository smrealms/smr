<?php declare(strict_types=1);

namespace Smr\Combat\Results\Damage;

/**
 * Outcome of weapon damage applied to a combatant that takes normal
 * shield, CD, and armour damage (Ship/Planet/Port).
 */
final class NormalTakenDamage extends TakenDamage {

	public function __construct(
		public bool $killingShot,
		public bool $targetAlreadyDead,
		public int $shieldDamage,
		int $combatDroneDamage,
		int $numCombatDrones,
		bool $hasCombatDrones,
		public int $armourDamage,
		int $totalDamage,
	) {
		parent::__construct(
			killingShot: $killingShot,
			targetAlreadyDead: $targetAlreadyDead,
			combatDroneDamage: $combatDroneDamage,
			numCombatDrones: $numCombatDrones,
			hasCombatDrones: $hasCombatDrones,
			totalDamage: $totalDamage,
		);
	}

}
