<?php declare(strict_types=1);

namespace SmrTest\lib\Combat;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Combat\NormalDamageTeamResultsResolver;
use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Damage\NormalDamageTeamTotals;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Results\Weapon\MissedWeaponResult;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Ship;

#[CoversClass(NormalDamageTeamResultsResolver::class)]
#[CoversClass(NormalDamageTeamTotals::class)]
class NormalDamageTeamResultsResolverTest extends TestCase {

	public function test_resolve_includes_weapon_and_drone_shield_damage(): void {
		$attacker = $this->createShip();
		$target = $this->createShip();
		$results = [
			CombatantResult::create(
				combatant: $attacker,
				weaponResults: [
					$this->createHit($target, shieldDamage: 2, armourDamage: 3),
					new MissedWeaponResult(new CombatDrones(1), $target),
				],
				dronesResult: $this->createHit($target, shieldDamage: 4, armourDamage: 5),
			),
		];

		$totals = NormalDamageTeamResultsResolver::resolve($results);

		self::assertSame(14, $totals->totalDamage);
		self::assertSame(6, $totals->shieldDamage);
		self::assertSame(8, $totals->getNonShieldDamage());
	}

	private function createShip(): Ship {
		return $this->createStub(Ship::class);
	}

	/**
	 * @return HitWeaponResult<Ship>
	 */
	private function createHit(
		Ship $target,
		int $shieldDamage,
		int $armourDamage,
	): HitWeaponResult {
		return new HitWeaponResult(
			weapon: new CombatDrones(1),
			target: $target,
			weaponDamage: new WeaponDamage(
				shieldDamage: $shieldDamage,
				armourDamage: $armourDamage,
				damageRollover: false,
			),
			actualDamage: new NormalTakenDamage(
				killingShot: false,
				targetAlreadyDead: false,
				shieldDamage: $shieldDamage,
				combatDroneDamage: 0,
				numCombatDrones: 0,
				hasCombatDrones: false,
				armourDamage: $armourDamage,
				totalDamage: $shieldDamage + $armourDamage,
			),
			killResult: null,
		);
	}

}
