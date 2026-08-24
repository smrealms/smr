<?php declare(strict_types=1);

namespace Smr\Combat\Results\Full;

use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Combatant\PortAttackerCombatResults;

readonly class PortFullCombatResults extends FullCombatResults {

	/**
	 * @param CombatantResult<\Smr\Combat\NormalCombatantInterface> $port
	 */
	public function __construct(
		public PortAttackerCombatResults $attackers,
		public CombatantResult $port,
	) {}

}
