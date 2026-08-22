<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Weapon\HitWeaponResult;

/**
 * @template-covariant TTarget of CombatantInterface
 */
class CombatantResult {

	/**
	 * @param array<int, \Smr\Combat\Results\Weapon\MissedWeaponResult|HitWeaponResult<TTarget>> $weaponResults
	 * @param ?HitWeaponResult<TTarget> $dronesResult
	 */
	public function __construct(
		public readonly CombatantInterface $combatant,
		public readonly bool $deadBeforeShot = false,
		public readonly array $weaponResults = [],
		public readonly ?HitWeaponResult $dronesResult = null,
	) {}

	/**
	 * @template TCreateTarget of CombatantInterface
	 * @param array<int, \Smr\Combat\Results\Weapon\MissedWeaponResult|HitWeaponResult<TCreateTarget>> $weaponResults
	 * @param ?HitWeaponResult<TCreateTarget> $dronesResult
	 * @return CombatantResult<TCreateTarget>
	 */
	public static function create(
		CombatantInterface $combatant,
		bool $deadBeforeShot = false,
		array $weaponResults = [],
		?HitWeaponResult $dronesResult = null,
	): self {
		return new self($combatant, $deadBeforeShot, $weaponResults, $dronesResult);
	}

	/**
	 * Returns the total actual damage done by this combatant in this round of combat.
	 */
	public function getTotalDamage(): int {
		return array_sum($this->getTotalDamagePerTarget());
	}

	/**
	 * @return array<int, int>
	 */
	public function getTotalDamagePerTarget(): array {
		$totals = [];
		$allResults = $this->weaponResults;
		if ($this->dronesResult !== null) {
			$allResults[] = $this->dronesResult;
		}
		foreach ($allResults as $result) {
			if ($result instanceof HitWeaponResult) {
				$targetID = $result->target->getCombatID();
				if (!array_key_exists($targetID, $totals)) {
					$totals[$targetID] = 0;
				}
				$totals[$targetID] += $result->actualDamage->totalDamage;
			}
		}
		return $totals;
	}

}
