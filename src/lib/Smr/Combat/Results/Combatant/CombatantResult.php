<?php declare(strict_types=1);

namespace Smr\Combat\Results\Combatant;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\NormalTakenDamage;
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
		foreach ($this->getHitWeaponResults() as $result) {
			$targetID = $result->target->getCombatID();
			if (!array_key_exists($targetID, $totals)) {
				$totals[$targetID] = 0;
			}
			$totals[$targetID] += $result->actualDamage->totalDamage;
		}
		return $totals;
	}

	/**
	 * Returns the total actual damage to shields by this combatant in this round of combat.
	 * Includes shield damage from both weapon and CD NormalDamage hits.
	 */
	public function getTotalShieldDamage(): int {
		$shieldDamage = 0;
		foreach ($this->getHitWeaponResults() as $result) {
			if ($result->actualDamage instanceof NormalTakenDamage) {
				$shieldDamage += $result->actualDamage->shieldDamage;
			}
		}
		return $shieldDamage;
	}

	/**
	 * @return iterable<HitWeaponResult<TTarget>>
	 */
	private function getHitWeaponResults(): iterable {
		foreach ($this->weaponResults as $result) {
			if ($result instanceof HitWeaponResult) {
				yield $result;
			}
		}
		if ($this->dronesResult !== null) {
			yield $this->dronesResult;
		}
	}

}
