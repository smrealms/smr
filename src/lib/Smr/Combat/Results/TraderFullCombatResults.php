<?php declare(strict_types=1);

namespace Smr\Combat\Results;

class TraderFullCombatResults extends FullCombatResults {

	/**
	 * @param TraderTeamCombatResults $attackers
	 * @param TraderTeamCombatResults $defenders
	 */
	public function __construct(
		public readonly array $attackers,
		public readonly array $defenders,
	) {}

}
