<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Damage\ForceTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Epoch;
use Smr\Force;
use Smr\Galaxy;
use Smr\Ship;
use SmrTest\TestUtils;

#[CoversClass(Force::class)]
class ForceTest extends TestCase {

	private Force $force;

	protected function setUp(): void {
		// Create an arbitrary empty force (we avoid using `getForce` for now
		// because of the call to the complicated function `tidyUpForces`).
		$this->force = TestUtils::constructPrivateClass(Force::class, 1, 2, 3);
	}

	public function test_constructor_properties(): void {
		self::assertSame(1, $this->force->getGameID());
		self::assertSame(2, $this->force->getSectorID());
		self::assertSame(3, $this->force->getOwnerID());
	}

	#[TestWith([0, false, 0])]
	#[TestWith([0, true, 0])]
	#[TestWith([9, false, 1])]
	#[TestWith([9, true, 0])]
	#[TestWith([24, false, 2])]
	#[TestWith([24, true, 1])]
	#[TestWith([25, false, 3])]
	#[TestWith([25, true, 2])]
	public function test_getBumpTurnCost(int $mines, bool $hasDCS, int $expected): void {
		$this->force->setMines($mines);
		$ship = $this->createStub(Ship::class);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getBumpTurnCost($ship));
	}

	#[TestWith([false, 3])]
	#[TestWith([true, 2])]
	public function test_getAttackTurnCost(bool $hasDCS, int $expected): void {
		$ship = $this->createStub(Ship::class);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getAttackTurnCost($ship));
	}

	public function test_add_and_take_SDs(): void {
		self::assertSame(0, $this->force->getSDs());
		self::assertFalse($this->force->hasSDs());
		$this->force->addSDs(Force::MAX_SDS);
		self::assertSame(Force::MAX_SDS, $this->force->getSDs());
		self::assertTrue($this->force->hasSDs());
		self::assertTrue($this->force->hasMaxSDs());
		$this->force->decreaseSDs(1);
		self::assertSame(Force::MAX_SDS - 1, $this->force->getSDs());
		self::assertTrue($this->force->hasSDs());
		self::assertFalse($this->force->hasMaxSDs());
	}

	public function test_add_and_take_CDs(): void {
		self::assertSame(0, $this->force->getCDs());
		self::assertFalse($this->force->hasCDs());
		$this->force->addCDs(Force::MAX_CDS);
		self::assertSame(Force::MAX_CDS, $this->force->getCDs());
		self::assertTrue($this->force->hasCDs());
		self::assertTrue($this->force->hasMaxCDs());
		$this->force->decreaseCDs(1);
		self::assertSame(Force::MAX_CDS - 1, $this->force->getCDs());
		self::assertTrue($this->force->hasCDs());
		self::assertFalse($this->force->hasMaxCDs());
	}

	public function test_add_and_take_mines(): void {
		self::assertSame(0, $this->force->getMines());
		self::assertFalse($this->force->hasMines());
		$this->force->addMines(Force::MAX_MINES);
		self::assertSame(Force::MAX_MINES, $this->force->getMines());
		self::assertTrue($this->force->hasMines());
		self::assertTrue($this->force->hasMaxMines());
		$this->force->decreaseMines(1);
		self::assertSame(Force::MAX_MINES - 1, $this->force->getMines());
		self::assertTrue($this->force->hasMines());
		self::assertFalse($this->force->hasMaxMines());
	}

	public function test_setForcesToMax(): void {
		self::assertSame(0, $this->force->getMines());
		self::assertSame(0, $this->force->getCDs());
		self::assertSame(0, $this->force->getSDs());
		$this->force->setForcesToMax();
		self::assertSame(Force::MAX_MINES, $this->force->getMines());
		self::assertSame(Force::MAX_CDS, $this->force->getCDs());
		self::assertSame(Force::MAX_SDS, $this->force->getSDs());
	}

	/**
	 * @param \Smr\Combat\Results\Damage\WeaponDamage $damage
	 * @param \Smr\Combat\Results\Damage\ForceTakenDamage $expected
	 */
	#[DataProvider('dataProvider_takeDamage')]
	public function test_takeDamage(string $case, WeaponDamage $damage, ForceTakenDamage $expected, int $mines, int $cds, int $sds): void {
		// Set up an unexpired stack with a specific number of forces
		$force = $this->createPartialMock(Force::class, ['hasExpired']);
		$force
			->expects(self::atMost(2))
			->method('hasExpired')
			->willReturn(false)
			->seal();
		$force->setMines($mines);
		$force->setCDs($cds);
		$force->setSDs($sds);
		// Test taking damage
		$result = $force->takeDamage($damage);
		self::assertEquals($expected, $result, $case);
	}

	/**
	 * @return array<array{0: string, 1: \Smr\Combat\Results\Damage\WeaponDamage, 2: \Smr\Combat\Results\Damage\ForceTakenDamage, 3: int, 4: int, 5: int}>
	 */
	public static function dataProvider_takeDamage(): array {
		return [
			[
				'Do overkill damage (e.g. 1000 drone damage)',
				new WeaponDamage(
					shieldDamage: 1000,
					armourDamage: 1000,
					damageRollover: true,
				),
				new ForceTakenDamage(
					killingShot: true,
					targetAlreadyDead: false,
					minesDamage: 200,
					numMines: 10,
					hasMines: false,
					combatDroneDamage: 30,
					numCombatDrones: 10,
					hasCombatDrones: false,
					scoutDroneDamage: 100,
					numScoutDrones: 5,
					hasScoutDrones: false,
					totalDamage: 330,
				),
				10, 10, 5,
			],
			[
				'Do exactly lethal damage (e.g. 330 drone damage)',
				new WeaponDamage(
					shieldDamage: 330,
					armourDamage: 330,
					damageRollover: true,
				),
				new ForceTakenDamage(
					killingShot: true,
					targetAlreadyDead: false,
					minesDamage: 200,
					numMines: 10,
					hasMines: false,
					combatDroneDamage: 30,
					numCombatDrones: 10,
					hasCombatDrones: false,
					scoutDroneDamage: 100,
					numScoutDrones: 5,
					hasScoutDrones: false,
					totalDamage: 330,
				),
				10, 10, 5,
			],
			[
				'Shield damage does nothing to forces',
				new WeaponDamage(
					shieldDamage: 100,
					armourDamage: 0,
					damageRollover: false,
				),
				new ForceTakenDamage(
					killingShot: false,
					targetAlreadyDead: false,
					minesDamage: 0,
					numMines: 0,
					hasMines: true,
					combatDroneDamage: 0,
					numCombatDrones: 0,
					hasCombatDrones: true,
					scoutDroneDamage: 0,
					numScoutDrones: 0,
					hasScoutDrones: true,
					totalDamage: 0,
				),
				10, 10, 5,
			],
			[
				'Overkill damage to mines only (e.g. armour weapon)',
				new WeaponDamage(
					shieldDamage: 0,
					armourDamage: 1000,
					damageRollover: false,
				),
				new ForceTakenDamage(
					killingShot: false,
					targetAlreadyDead: false,
					minesDamage: 200,
					numMines: 10,
					hasMines: false,
					combatDroneDamage: 0,
					numCombatDrones: 0,
					hasCombatDrones: true,
					scoutDroneDamage: 0,
					numScoutDrones: 0,
					hasScoutDrones: true,
					totalDamage: 200,
				),
				10, 10, 5,
			],
			[
				'Overkill damage to CDs only (e.g. armour weapon)',
				new WeaponDamage(
					shieldDamage: 0,
					armourDamage: 1000,
					damageRollover: false,
				),
				new ForceTakenDamage(
					killingShot: false,
					targetAlreadyDead: false,
					minesDamage: 0,
					numMines: 0,
					hasMines: false,
					combatDroneDamage: 30,
					numCombatDrones: 10,
					hasCombatDrones: false,
					scoutDroneDamage: 0,
					numScoutDrones: 0,
					hasScoutDrones: true,
					totalDamage: 30,
				),
				0, 10, 5,
			],
			[
				'Overkill damage to SDs only (e.g. armour weapon)',
				new WeaponDamage(
					shieldDamage: 0,
					armourDamage: 1000,
					damageRollover: false,
				),
				new ForceTakenDamage(
					killingShot: true,
					targetAlreadyDead: false,
					minesDamage: 0,
					numMines: 0,
					hasMines: false,
					combatDroneDamage: 0,
					numCombatDrones: 0,
					hasCombatDrones: false,
					scoutDroneDamage: 100,
					numScoutDrones: 5,
					hasScoutDrones: false,
					totalDamage: 100,
				),
				0, 0, 5,
			],
			[
				'Target is already dead',
				new WeaponDamage(
					shieldDamage: 0,
					armourDamage: 1000,
					damageRollover: true,
				),
				new ForceTakenDamage(
					killingShot: false,
					targetAlreadyDead: true,
					minesDamage: 0,
					numMines: 0,
					hasMines: false,
					combatDroneDamage: 0,
					numCombatDrones: 0,
					hasCombatDrones: false,
					scoutDroneDamage: 0,
					numScoutDrones: 0,
					hasScoutDrones: false,
					totalDamage: 0,
				),
				0, 0, 0,
			],
		];
	}

	#[DataProvider('dataProvider_getMaxExpireTime')]
	public function test_getMaxExpireTime(int $sds, int $cds, int $mines, int $galMaxForceTime, int $expected): void {
		// Stub the galaxy that this force is inside
		$galaxy = $this->createStub(Galaxy::class);
		$galaxy->method('getMaxForceTime')->willReturn($galMaxForceTime);

		// Partially mock the force so we can use the galaxy stub
		$force = $this->createPartialMock($this->force::class, ['getGalaxy']);
		$force
			->expects(self::atMost(1))
			->method('getGalaxy')
			->willReturn($galaxy)
			->seal();

		// Set the number of forces, and check result
		$force->setSDs($sds);
		$force->setCDs($cds);
		$force->setMines($mines);
		self::assertSame($expected, $force->getMaxExpireTime());
	}

	/**
	 * @return array<array<int>>
	 */
	public static function dataProvider_getMaxExpireTime(): array {
		$above = Force::LOWEST_MAX_EXPIRE_SCOUTS_ONLY + 1;
		$below = Force::LOWEST_MAX_EXPIRE_SCOUTS_ONLY - 1;
		return [
			// sds, cds, mines, galaxy max expire time, expected max expire time
			[1, 0, 0, $above, $above],
			[1, 0, 0, $below, Force::LOWEST_MAX_EXPIRE_SCOUTS_ONLY],
			[1, 1, 0, $below, $below],
			[1, 0, 1, $below, $below],
			[1, 1, 1, $below, $below],
			[0, 1, 0, $below, $below],
			[0, 0, 1, $below, $below],
			[0, 1, 1, $below, $below],
			[0, 0, 0, $below, 0],
		];
	}

	#[DataProvider('dataProvider_updateExpire')]
	public function test_updateExpire(int $sds, int $cds, int $mines, int $galMaxForceTime, int $expectedExpire): void {
		// Stub the galaxy that this force is inside
		$galaxy = $this->createStub(Galaxy::class);
		$galaxy->method('getMaxForceTime')->willReturn($galMaxForceTime);

		// Partially mock the force so we can use the galaxy stub
		$force = $this->createPartialMock($this->force::class, ['getGalaxy']);
		$force
			->expects(self::atMost(2))
			->method('getGalaxy')
			->willReturn($galaxy)
			->seal();

		// Set the number of forces, and check result
		$force->setSDs($sds);
		$force->setCDs($cds);
		$force->setMines($mines);
		$force->updateExpire();
		self::assertSame($expectedExpire, $force->getExpire() - Epoch::time());
	}

	/**
	 * @return array<array<int>>
	 */
	public static function dataProvider_updateExpire(): array {
		$day = 86400;
		// sds, cds, mines, galaxy max expire time, expected expire time
		return [
			[0, 0, 0, $day, 0],
			// Scouts independent of gal max expire time
			[1, 0, 0, 0, 1 * $day],
			[5, 0, 0, 0, 5 * $day],
			// Non-scout-only goes up to gal max expire time
			[5, 50, 50, 7 * $day, 7 * $day],
			// Individual cd/mine add 1/50th
			[0, 1, 0, 50 * $day, $day],
			[0, 0, 1, 50 * $day, $day],
			[0, 49, 0, 50 * $day, 49 * $day],
			[0, 0, 49, 50 * $day, 49 * $day],
			[0, 50, 0, $day, $day],
			[0, 0, 50, $day, $day],
			[1, 1, 1, 50 * $day, 3 * $day],
			[0, 25, 24, 50 * $day, 49 * $day],
			[0, 25, 25, 50 * $day, 50 * $day],
		];
	}

}
