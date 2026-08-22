<?php declare(strict_types=1);

namespace Smr\Combat\Results\Full;

use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Combatant\PlanetAttackerCombatResults;

readonly class PlanetFullCombatResults extends FullCombatResults {

	/**
	 * @param CombatantResult<\Smr\Combat\NormalCombatantInterface> $planet
	 */
	public function __construct(
		public PlanetAttackerCombatResults $attackers,
		public CombatantResult $planet,
	) {}

}
