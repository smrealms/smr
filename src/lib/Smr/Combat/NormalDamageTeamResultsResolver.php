<?php declare(strict_types=1);

namespace Smr\Combat;

use Smr\Combat\Results\Damage\NormalDamageTeamTotals;

/**
 * Aggregates the normal damage dealt by a team in one round of combat.
 */
final class NormalDamageTeamResultsResolver {

	/**
	 * @param array<int, \Smr\Combat\Results\Combatant\CombatantResult<NormalCombatantInterface>> $combatantResults
	 */
	public static function resolve(array $combatantResults): NormalDamageTeamTotals {
		$totalDamage = 0;
		$shieldDamage = 0;
		foreach ($combatantResults as $combatantResult) {
			$totalDamage += $combatantResult->getTotalDamage();
			$shieldDamage += $combatantResult->getTotalShieldDamage();
		}
		return new NormalDamageTeamTotals(
			totalDamage: $totalDamage,
			shieldDamage: $shieldDamage,
		);
	}

}
