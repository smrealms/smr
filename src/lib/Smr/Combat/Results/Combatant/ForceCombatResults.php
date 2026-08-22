<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

final readonly class ForceCombatResults {

	public int $totalDamage;

	/**
	 * @param array{Mines?: \Smr\Combat\Results\Weapon\HitWeaponResult<\Smr\AbstractShip>, Drones?: \Smr\Combat\Results\Weapon\HitWeaponResult<\Smr\AbstractShip>, Scouts?: \Smr\Combat\Results\Weapon\HitWeaponResult<\Smr\AbstractShip>} $results
	 */
	public function __construct(
		public array $results,
		public bool $deadBeforeShot,
		public bool $forceDestroyed,
	) {
		// Accumulate total damage across mines/drones/scouts.
		$totalDamage = 0;
		foreach ($this->results as $result) {
			$totalDamage += $result->actualDamage->totalDamage;
		}
		$this->totalDamage = $totalDamage;
	}

}
