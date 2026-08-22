<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

/**
 * @extends TeamCombatResults<\Smr\Combat\NormalCombatantInterface>
 */
final readonly class PortAttackerCombatResults extends TeamCombatResults {

	/**
	 * @param array<int, CombatantResult<\Smr\Combat\NormalCombatantInterface>> $traders
	 */
	public function __construct(int $totalDamage, array $traders, public int $downgrades) {
		parent::__construct($totalDamage, $traders);
	}

}
