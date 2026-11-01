<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

use Smr\Combat\CombatantInterface;

/**
 * @template-covariant TTarget of \Smr\Combat\CombatantInterface
 */
readonly class TeamCombatResults {

	/**
	 * @param array<int, CombatantResult<TTarget>> $traders
	 */
	public function __construct(
		public int $totalDamage,
		public array $traders,
	) {}

	/**
	 * Is combatant a member of this team?
	 */
	public function hasCombatant(CombatantInterface $combatant): bool {
		return array_any(
			$this->traders,
			fn($trader) => $trader->combatant->getCombatID() === $combatant->getCombatID(),
		);
	}

	/**
	 * Get total damage done to an enemy combatant by this team.
	 */
	public function getTotalDamageDealtTo(CombatantInterface $target): int {
		$totalDamageTaken = 0;
		$targetID = $target->getCombatID();
		foreach ($this->traders as $trader) {
			$totalDamageTaken += $trader->getTotalDamagePerTarget()[$targetID] ?? 0;
		}
		return $totalDamageTaken;
	}

}
