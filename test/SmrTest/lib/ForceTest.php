<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Force;
use Smr\Galaxy;
use SmrTest\TestUtils;

/**
 * @covers Smr\Force
 */
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

	/**
	 * @dataProvider provider_getBumpTurnCost
	 */
	public function test_getBumpTurnCost(int $mines, bool $hasDCS, int $expected): void {
		$this->force->setMines($mines);
		$ship = $this->createPartialMock(AbstractShip::class, ['hasDCS', 'isFederal']);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getBumpTurnCost($ship));
	}

	/**
	 * @return array<array{int, bool, int}>
	 */
	public function provider_getBumpTurnCost(): array {
		return [
			[0, false, 0],
			[0, true, 0],
			[9, false, 1],
			[9, true, 0],
			[24, false, 2],
			[24, true, 1],
			[25, false, 3],
			[25, true, 2],
		];
	}

	/**
	 * @dataProvider provider_getAttackTurnCost
	 */
	public function test_getAttackTurnCost(bool $hasDCS, int $expected): void {
		$ship = $this->createPartialMock(AbstractShip::class, ['hasDCS', 'isFederal']);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getAttackTurnCost($ship));
	}

	/**
	 * @return array<array{bool, int}>
	 */
	public function provider_getAttackTurnCost(): array {
		return [
			[false, 3],
			[true, 2],
		];
	}

	public function test_add_and_take_SDs(): void {
		self::assertSame(0, $this->force->getSDs());
		self::assertFalse($this->force->hasSDs());
		$this->force->addSDs(Force::MAX_SDS);
		self::assertSame(Force::MAX_SDS, $this->force->getSDs());
		self::assertTrue($this->force->hasSDs());
		self::assertTrue($this->force->hasMaxSDs());
		$this->force->takeSDs(1);
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
		$this->force->takeCDs(1);
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
		$this->force->takeMines(1);
		self::assertSame(Force::MAX_MINES - 1, $this->force->getMines());
		self::assertTrue($this->force->hasMines());
		self::assertFalse($this->force->hasMaxMines());
	}

	/**
	 * @dataProvider dataProvider_takeDamage
	 *
	 * @param WeaponDamageData $damage
	 * @param ForceTakenDamageData $expected
	 */
	public function test_takeDamage(string $case, array $damage, array $expected, int $mines, int $cds, int $sds): void {
		// Set up an unexpired stack with a specific number of forces
		$force = $this->createPartialMock($this->force::class, ['hasExpired']);
		$force->method('hasExpired')->willReturn(false);
		$force->setMines($mines);
		$force->setCDs($cds);
		$force->setSDs($sds);
		// Test taking damage
		$result = $force->takeDamage($damage);
		self::assertSame($expected, $result, $case);
	}

	/**
	 * @return array<array{0: string, 1: WeaponDamageData, 2: ForceTakenDamageData, 3: int, 4: int, 5: int}>
	 */
	public function dataProvider_takeDamage(): array {
		return [
			[
				'Do overkill damage (e.g. 1000 drone damage)',
				[
					'Shield' => 1000,
					'Armour' => 1000,
					'Rollover' => true,
				],
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Mines' => 200,
					'NumMines' => 10,
					'HasMines' => false,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'SDs' => 100,
					'NumSDs' => 5,
					'HasSDs' => false,
					'TotalDamage' => 330,
				],
				10, 10, 5,
			],
			[
				'Do exactly lethal damage (e.g. 330 drone damage)',
				[
					'Shield' => 330,
					'Armour' => 330,
					'Rollover' => true,
				],
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Mines' => 200,
					'NumMines' => 10,
					'HasMines' => false,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'SDs' => 100,
					'NumSDs' => 5,
					'HasSDs' => false,
					'TotalDamage' => 330,
				],
				10, 10, 5,
			],
			[
				'Shield damage does nothing to forces',
				[
					'Shield' => 100,
					'Armour' => 0,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Mines' => 0,
					'NumMines' => 0,
					'HasMines' => true,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'SDs' => 0,
					'NumSDs' => 0,
					'HasSDs' => true,
					'TotalDamage' => 0,
				],
				10, 10, 5,
			],
			[
				'Overkill damage to mines only (e.g. armour weapon)',
				[
					'Shield' => 0,
					'Armour' => 1000,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Mines' => 200,
					'NumMines' => 10,
					'HasMines' => false,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'SDs' => 0,
					'NumSDs' => 0,
					'HasSDs' => true,
					'TotalDamage' => 200,
				],
				10, 10, 5,
			],
			[
				'Overkill damage to CDs only (e.g. armour weapon)',
				[
					'Shield' => 0,
					'Armour' => 1000,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Mines' => 0,
					'NumMines' => 0,
					'HasMines' => false,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'SDs' => 0,
					'NumSDs' => 0,
					'HasSDs' => true,
					'TotalDamage' => 30,
				],
				0, 10, 5,
			],
			[
				'Overkill damage to SDs only (e.g. armour weapon)',
				[
					'Shield' => 0,
					'Armour' => 1000,
					'Rollover' => false,
				],
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Mines' => 0,
					'NumMines' => 0,
					'HasMines' => false,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'SDs' => 100,
					'NumSDs' => 5,
					'HasSDs' => false,
					'TotalDamage' => 100,
				],
				0, 0, 5,
			],
			[
				'Target is already dead',
				[
					'Shield' => 0,
					'Armour' => 1000,
					'Rollover' => true,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => true,
					'Mines' => 0,
					'NumMines' => 0,
					'HasMines' => false,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'SDs' => 0,
					'NumSDs' => 0,
					'HasSDs' => false,
					'TotalDamage' => 0,
				],
				0, 0, 0,
			],
		];
	}

	/**
	 * @dataProvider dataProvider_getMaxExpireTime
	 */
	public function test_getMaxExpireTime(int $sds, int $cds, int $mines, int $galMaxForceTime, int $expected): void {
		// Stub the galaxy that this force is inside
		$galaxy = $this->createStub(Galaxy::class);
		$galaxy->method('getMaxForceTime')->willReturn($galMaxForceTime);

		// Partially mock the force so we can use the galaxy stub
		$force = $this->createPartialMock($this->force::class, ['getGalaxy']);
		$force->method('getGalaxy')->willReturn($galaxy);

		// Set the number of forces, and check result
		$force->setSDs($sds);
		$force->setCDs($cds);
		$force->setMines($mines);
		self::assertSame($expected, $force->getMaxExpireTime());
	}

	/**
	 * @return array<array<int>>
	 */
	public function dataProvider_getMaxExpireTime(): array {
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

}
