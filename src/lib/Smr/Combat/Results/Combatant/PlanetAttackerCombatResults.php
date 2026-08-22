<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

/** @extends TeamCombatResults<\Smr\Combat\NormalCombatantInterface> */
final readonly class PlanetAttackerCombatResults extends TeamCombatResults {

	/**
	 * @param array<int, CombatantResult<\Smr\Combat\NormalCombatantInterface>> $traders
	 * @param array<int, int> $downgrades
	 */
	public function __construct(int $totalDamage, array $traders, public array $downgrades) {
		parent::__construct($totalDamage, $traders);
	}

}
