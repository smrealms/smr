<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Weapon\ScoutDrones;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Ship;

#[CoversClass(ScoutDrones::class)]
class ScoutDronesTest extends TestCase {

	public function test_getAmount(): void {
		$sds = new ScoutDrones(100);
		self::assertSame(100, $sds->getAmount());
	}

	public function test_getShieldDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getArmourDamage());
	}

	public function test_getBaseAccuracy(): void {
		$sds = new ScoutDrones(100);
		self::assertSame(100, $sds->getBaseAccuracy());
	}

	#[TestWith([0, 96.0])]
	#[TestWith([10, 86.0])]
	public function test_getModifiedForceAccuracyAgainstPlayer_reduces_accuracy_for_player_level_and_randomness(
		int $level,
		float $expected,
	): void {
		$drones = $this->createDrones();
		$force = $this->createStub(Force::class);
		$targetShip = $this->createShip(level: $level);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($force, $targetShip);
		self::assertSame($expected, $result);
	}

	public function test_getModifiedForceDamageAgainstPlayer_applies_launched_drone_damage(): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$targetShip = $this->createShip();
		srand(1);

		$expected = new WeaponDamage(
			shieldDamage: 200,
			armourDamage: 200,
			damageRollover: false,
			launched: 10,
		);
		$result = $drones->getModifiedDamageAgainstTarget($forces, $targetShip);

		self::assertEquals($expected, $result);
	}

	public function test_shoot_applies_damage_and_consumes_launched_drones(): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$ship = $this->createShip();
		$takenDamage = new NormalTakenDamage(false, false, 0, 0, 0, false, 0, 200);
		$ship->method('takeDamage')->willReturn($takenDamage);
		srand(1);

		$result = $drones->shoot(
			new WeaponShotAtCombatant($forces, $ship, static fn() => throw new LogicException()),
		);

		self::assertInstanceOf(HitWeaponResult::class, $result);
		self::assertEquals($takenDamage, $result->actualDamage);
		self::assertSame(0, $drones->getAmount());
	}

	private function createDrones(): ScoutDrones {
		return new ScoutDrones(10);
	}

	private function createShip(int $level = 0): Ship&Stub {
		$ship = $this->createStub(Ship::class);
		$ship->method('getLevel')->willReturn($level);
		return $ship;
	}

}
