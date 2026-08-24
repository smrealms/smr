<?php declare(strict_types=1);

namespace Smr\Combat\Results\Full;

use Smr\Combat\Results\Combatant\TeamCombatResults;

readonly class TraderFullCombatResults extends FullCombatResults {

	/**
	 * @param TeamCombatResults<\Smr\Combat\NormalCombatantInterface> $attackers
	 * @param TeamCombatResults<\Smr\Combat\NormalCombatantInterface> $defenders
	 */
	public function __construct(
		public TeamCombatResults $attackers,
		public TeamCombatResults $defenders,
	) {}

}
