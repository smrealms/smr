<?php declare(strict_types=1);

namespace Smr\Combat\Results;

class PlanetFullCombatResults extends FullCombatResults {

	/**
	 * @param PlanetAttackerCombatResults $attackers
	 * @param PlanetCombatResults $planet
	 */
	public function __construct(
		public readonly array $attackers,
		public readonly array $planet,
	) {}

}
