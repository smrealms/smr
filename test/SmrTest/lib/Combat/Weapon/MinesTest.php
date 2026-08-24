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
use Smr\Combat\Weapon\Mines;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Sector;
use Smr\Ship;

#[CoversClass(Mines::class)]
class MinesTest extends TestCase {

	public function test_getAmount(): void {
		$mines = new Mines(100);
		self::assertSame(100, $mines->getAmount());
	}

	public function test_getShieldDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getArmourDamage());
	}

	public function test_getBaseAccuracy(): void {
		$mines = new Mines(100);
		self::assertSame(100, $mines->getBaseAccuracy());
	}

	#[TestWith([0, 0, false, 96.0])]
	#[TestWith([0, 10, false, 86.0])]
	#[TestWith([25, 10, false, 87.0])]
	#[TestWith([0, 10, true, 37.433674221733])]
	public function test_getModifiedForceAccuracyAgainstPlayer_reduces_accuracy_for_player_level_and_randomness(
		int $numberOfMines,
		int $level,
		bool $minesAreAttacker,
		float $expected,
	): void {
		$mines = new Mines(10);
		$sector = $this->createStub(Sector::class);
		$enemyForces = [];
		if ($numberOfMines > 0) {
			$enemyForce = $this->createStub(Force::class);
			$enemyForce->method('getMines')->willReturn($numberOfMines);
			$enemyForces[] = $enemyForce;
		}
		$sector->method('getEnemyForces')->willReturn($enemyForces);
		$sector->method('getNumberOfConnections')->willReturn(4);
		$forces = $this->createStub(Force::class);
		$forces->method('getSector')->willReturn($sector);
		$targetShip = $this->createShip($level);
		srand(1);

		$result = $mines->getModifiedForceAccuracyAgainstPlayer($forces, $targetShip, $minesAreAttacker);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([false, false, 100])]
	#[TestWith([false, true, 75])]
	#[TestWith([true, false, 50])]
	#[TestWith([true, true, 40])]
	public function test_getModifiedForceDamageAgainstPlayer_applies_federal_and_dcs_modifiers(
		bool $isFederal,
		bool $hasDcs,
		int $damage,
	): void {
		$mines = $this->createMines(accuracy: 50);
		$force = $this->createStub(Force::class);
		$targetShip = $this->createShip(isFederal: $isFederal, hasDcs: $hasDcs);

		$expected = new WeaponDamage(
			shieldDamage: $damage,
			armourDamage: $damage,
			damageRollover: false,
			launched: 5,
		);
		$result = $mines->getModifiedDamageAgainstTarget($force, $targetShip);
		self::assertEquals($expected, $result);
	}

	public function test_shoot_consumes_only_mines_needed_for_damage(): void {
		$mines = $this->createMines(accuracy: 50);
		$forces = $this->createStub(Force::class);
		$ship = $this->createShip();
		// Five launched mines deal 100 shield damage; only 40 damage was taken.
		$takenDamage = new NormalTakenDamage(false, false, 0, 0, 0, false, 0, 40);
		$ship->method('takeDamageFromMines')->willReturn($takenDamage);

		$result = $mines->shoot(
			new WeaponShotAtCombatant($forces, $ship, static fn() => throw new LogicException()),
		);

		self::assertInstanceOf(HitWeaponResult::class, $result);
		self::assertSame($takenDamage, $result->actualDamage);
		self::assertSame(2, $result->weaponDamage->launched);
		// The two adjusted launches are subtracted from the original ten mines.
		self::assertSame(8, $mines->getAmount());
	}

	// Mines test double with fixed accuracy to avoid randomness in damage calculations
	private function createMines(int $accuracy): Mines {
		return new class ($accuracy) extends Mines {

			public function __construct(private readonly int $fixedAccuracy) {
				parent::__construct(10);
			}

			public function getModifiedForceAccuracyAgainstPlayer(
				Force $forces,
				Ship $target,
				bool $minesAreAttacker,
			): float {
				return $this->fixedAccuracy;
			}
		};
	}

	private function createShip(
		int $level = 0,
		bool $isFederal = false,
		bool $hasDcs = false,
	): Ship&Stub {
		$ship = $this->createStub(Ship::class);
		$ship->method('getLevel')->willReturn($level);
		$ship->method('isFederal')->willReturn($isFederal);
		$ship->method('hasDCS')->willReturn($hasDcs);
		return $ship;
	}

}
