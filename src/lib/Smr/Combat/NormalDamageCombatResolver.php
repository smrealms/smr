<?php declare(strict_types=1);

namespace Smr\Combat;

use Closure;
use Smr\Combat\Results\Combatant\CombatantResult;

/**
 * Resolves a normal-damage combat round for ships, ports, and planets.
 */
final class NormalDamageCombatResolver {

	/**
	 * @template TShooter of NormalCombatantInterface
	 * @param TShooter $shooter
	 * @param Closure(): NormalCombatantInterface<TShooter> $selectWeaponTarget
	 * @param ?Closure(): NormalCombatantInterface<TShooter> $selectDroneTarget
	 * @return CombatantResult<NormalCombatantInterface>
	 */
	public static function shoot(
		NormalCombatantInterface $shooter,
		Closure $selectWeaponTarget,
		?Closure $selectDroneTarget = null,
	): CombatantResult {
		if ($shooter->isDestroyed()) {
			/** @var array<int, \Smr\Combat\Results\Weapon\HitWeaponResult<NormalCombatantInterface>> $emptyResults*/
			$emptyResults = [];
			return CombatantResult::create(
				combatant: $shooter,
				deadBeforeShot: true,
				weaponResults: $emptyResults,
			);
		}

		$weaponResults = [];
		foreach ($shooter->getWeapons() as $orderID => $weapon) {
			$target = $selectWeaponTarget();
			$weaponResults[$orderID] = $weapon->shoot(
				new WeaponShotAtCombatant(
					shooter: $shooter,
					target: $target,
					resolveKill: fn() => $target->killBy($shooter),
				),
			);
		}

		$dronesResult = null;
		if ($shooter->hasCDs()) {
			$target = ($selectDroneTarget ?? $selectWeaponTarget)();
			$dronesResult = $shooter->createCombatDrones()->shoot(
				new WeaponShotAtCombatant(
					shooter: $shooter,
					target: $target,
					resolveKill: fn() => $target->killBy($shooter),
				),
			);
		}

		return CombatantResult::create(
			combatant: $shooter,
			weaponResults: $weaponResults,
			dronesResult: $dronesResult,
		);
	}

}
