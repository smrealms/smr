<?php declare(strict_types=1);

namespace Smr\Combat\Results\Damage;

/**
 * Outcome of weapon damage applied to Forces (i.e. mine/CD/SD stacks).
 */
final class ForceTakenDamage extends TakenDamage {

	public function __construct(
		bool $killingShot,
		bool $targetAlreadyDead,
		public int $minesDamage,
		public int $numMines,
		public bool $hasMines,
		int $combatDroneDamage,
		int $numCombatDrones,
		bool $hasCombatDrones,
		public int $scoutDroneDamage,
		public int $numScoutDrones,
		public bool $hasScoutDrones,
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
