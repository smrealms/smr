<?php declare(strict_types=1);

namespace Smr\Combat\Results\Damage;

/**
 * Stores normal damage dealt by one team of combatants.
 */
final readonly class NormalDamageTeamTotals {

	public function __construct(
		public int $totalDamage,
		public int $shieldDamage,
	) {}

	/**
	 * Damage that can contribute to port or planet downgrades.
	 */
	public function getNonShieldDamage(): int {
		return $this->totalDamage - $this->shieldDamage;
	}

}
