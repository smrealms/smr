<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Results\Combatant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Results\Weapon\MissedWeaponResult;
use Smr\Combat\Weapon\CombatDrones;

#[CoversClass(CombatantResult::class)]
class CombatantResultTest extends TestCase {

	public function test_getTotalDamagePerTarget_combines_weapon_and_drone_hits(): void {
		$shooter = $this->createShip(1);
		$firstTarget = $this->createShip(2);
		$secondTarget = $this->createShip(3);

		// Deal 7 damage to firstTarget over 2 weapon shots, and 5 damage to secondTarget
		// (All weapons are CDs for simplicity, but does not matter for this test.)
		$result = CombatantResult::create(
			combatant: $shooter,
			weaponResults: [
				$this->createHit($firstTarget, 4),
				new MissedWeaponResult(new CombatDrones(1), $firstTarget),
				$this->createHit($firstTarget, 3),
			],
			dronesResult: $this->createHit($secondTarget, 5),
		);

		self::assertSame([2 => 7, 3 => 5], $result->getTotalDamagePerTarget());
		self::assertSame(12, $result->getTotalShieldDamage());
	}

	public function test_getTotalDamage_sums_damage_across_targets(): void {
		$shooter = $this->createShip(1);
		$firstTarget = $this->createShip(2);
		$secondTarget = $this->createShip(3);

		$result = CombatantResult::create(
			combatant: $shooter,
			weaponResults: [
				$this->createHit($firstTarget, 4),
				$this->createHit($secondTarget, 5),
			],
		);

		self::assertSame(9, $result->getTotalDamage());
	}

	private function createShip(int $combatID): AbstractShip&Stub {
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('getCombatID')->willReturn($combatID);
		return $ship;
	}

	/**
	 * @return HitWeaponResult<AbstractShip>
	 */
	private function createHit(AbstractShip $target, int $totalDamage): HitWeaponResult {
		return new HitWeaponResult(
			weapon: new CombatDrones(1),
			target: $target,
			weaponDamage: new WeaponDamage(
				shieldDamage: $totalDamage,
				armourDamage: 0,
				damageRollover: false,
			),
			actualDamage: new NormalTakenDamage(
				killingShot: false,
				targetAlreadyDead: false,
				shieldDamage: $totalDamage,
				combatDroneDamage: 0,
				numCombatDrones: 0,
				hasCombatDrones: false,
				armourDamage: 0,
				totalDamage: $totalDamage,
			),
			killResult: null,
		);
	}

}
