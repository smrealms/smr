<?php declare(strict_types=1);

namespace Smr\Combat\Results;

class ForceFullCombatResults extends FullCombatResults {

	/**
	 * @param ForceAttackerCombatResults $attackers
	 * @param ForceCombatResults $forces
	 */
	public function __construct(
		public readonly array $attackers,
		public readonly array $forces,
		public readonly bool $bump,
	) {}

}
