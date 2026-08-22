<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

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

}
