<?php declare(strict_types=1);

namespace Smr\Combat\Results;

class PortFullCombatResults extends FullCombatResults {

	/**
	 * @param PortAttackerCombatResults $attackers
	 * @param PortCombatResults $port
	 */
	public function __construct(
		public readonly array $attackers,
		public readonly array $port,
	) {}

}
