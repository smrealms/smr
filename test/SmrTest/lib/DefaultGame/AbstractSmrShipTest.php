<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPlayer;
use AbstractSmrShip;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Smr\ShipClass;

/**
 * This test is expected to not make any changes to the database.
 *
 * @covers AbstractSmrShip
 */
class AbstractSmrShipTest extends TestCase {

	private AbstractSmrPlayer&MockObject $player; // will be mocked

	protected function setUp(): void {
		// Create mock player that will be needed to create any ship
		$this->player = $this->createMock(AbstractSmrPlayer::class);
		$this->player
			->method('getAccountID')
			->willReturn(7);
		$this->player
			->method('getGameID')
			->willReturn(3);
		// Use Demonica because it's the only ship with all special hardware
		$this->player
			->method('getShipTypeID')
			->willReturn(SHIP_TYPE_DEMONICA);
	}

	public function test_base_ship_properties_are_set_correctly(): void {
		$ship = new AbstractSmrShip($this->player);
		self::assertSame('Demonica', $ship->getName());
		self::assertSame(SHIP_TYPE_DEMONICA, $ship->getTypeID());
		self::assertSame(ShipClass::HUNTER, $ship->getClassID());
		self::assertSame(6, $ship->getHardpoints());
		self::assertSame(10, $ship->getType()->getSpeed());
		self::assertSame(0, $ship->getCost());
	}

	public function test_cloak(): void {
		$ship = new AbstractSmrShip($this->player);

		// ships are initially uncloaked
		self::assertFalse($ship->isCloaked());

		// remain uncloaked when disabled without hardware
		$ship->decloak();
		self::assertFalse($ship->isCloaked());

		// add cloak hardware
		$ship->increaseHardware(HARDWARE_CLOAK, 1);
		self::assertFalse($ship->isCloaked());
		// enable
		$ship->enableCloak();
		self::assertTrue($ship->isCloaked());
		// disable
		$ship->decloak();
		self::assertFalse($ship->isCloaked());
	}

	public function test_cloak_throws_when_missing_hardware(): void {
		$ship = new AbstractSmrShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->enableCloak();
	}

	public function test_illusion_generator(): void {
		$ship = new AbstractSmrShip($this->player);

		// ship has no IG initially
		self::assertFalse($ship->getIllusionShip());

		// remain unset when disabled without hardware
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());

		// add IG hardware
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertFalse($ship->getIllusionShip());
		// enable
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
		$expected = [
			'ID' => SHIP_TYPE_THIEF,
			'Attack' => 12,
			'Defense' => 13,
		];
		self::assertSame($expected, $ship->getIllusionShip());
		self::assertSame($expected['ID'], $ship->getIllusionShipID());
		self::assertSame($expected['Attack'], $ship->getIllusionAttack());
		self::assertSame($expected['Defense'], $ship->getIllusionDefense());
		self::assertSame('Thief', $ship->getIllusionShipName());
		// disable
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());
	}

	public function test_illusion_throws_when_missing_hardware(): void {
		$ship = new AbstractSmrShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
	}

	public function test_hardware(): void {
		$ship = new AbstractSmrShip($this->player);

		// shields
		self::assertSame(0, $ship->getShields());
		$ship->increaseShields(10);
		self::assertSame(10, $ship->getShields());
		$ship->increaseShields(5);
		self::assertSame(15, $ship->getShields());
		$ship->decreaseShields(10);
		self::assertSame(5, $ship->getShields());

		// armour
		self::assertSame(0, $ship->getArmour());
		$ship->increaseArmour(10);
		self::assertSame(10, $ship->getArmour());
		$ship->increaseArmour(5);
		self::assertSame(15, $ship->getArmour());
		$ship->decreaseArmour(10);
		self::assertSame(5, $ship->getArmour());

		// CDs
		self::assertSame(0, $ship->getCDs());
		$ship->increaseCDs(10);
		self::assertSame(10, $ship->getCDs());
		$ship->increaseCDs(5);
		self::assertSame(15, $ship->getCDs());
		$ship->decreaseCDs(10);
		self::assertSame(5, $ship->getCDs());

		// Mines
		self::assertSame(0, $ship->getMines());
		$ship->increaseMines(10);
		self::assertSame(10, $ship->getMines());
		$ship->increaseMines(5);
		self::assertSame(15, $ship->getMines());
		$ship->decreaseMines(10);
		self::assertSame(5, $ship->getMines());

		// SDs
		self::assertSame(0, $ship->getSDs());
		$ship->increaseSDs(10);
		self::assertSame(10, $ship->getSDs());
		$ship->increaseSDs(5);
		self::assertSame(15, $ship->getSDs());
		$ship->decreaseSDs(10);
		self::assertSame(5, $ship->getSDs());

		// Cloak
		self::assertTrue($ship->getType()->canHaveCloak());
		self::assertFalse($ship->hasCloak());
		$ship->increaseHardware(HARDWARE_CLOAK, 1);
		self::assertTrue($ship->hasCloak());

		// Illusion
		self::assertTrue($ship->getType()->canHaveIllusion());
		self::assertFalse($ship->hasIllusion());
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertTrue($ship->hasIllusion());

		// Jump
		self::assertTrue($ship->getType()->canHaveJump());
		self::assertFalse($ship->hasJump());
		$ship->increaseHardware(HARDWARE_JUMP, 1);
		self::assertTrue($ship->hasJump());

		// Scanner
		self::assertTrue($ship->getType()->canHaveScanner());
		self::assertFalse($ship->hasScanner());
		$ship->increaseHardware(HARDWARE_SCANNER, 1);
		self::assertTrue($ship->hasScanner());

		// DCs
		self::assertTrue($ship->getType()->canHaveDCS());
		self::assertFalse($ship->hasDCS());
		$ship->increaseHardware(HARDWARE_DCS, 1);
		self::assertTrue($ship->hasDCS());
	}

	/**
	 * @dataProvider dataProvider_takeDamage
	 */
	public function test_takeDamage(string $case, array $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a ship with a fixed amount of defenses
		$this->player
			->method('isDead')
			->willReturn($armour == 0);
		$ship = new AbstractSmrShip($this->player);
		$ship->setShields($shields);
		$ship->setCDs($cds);
		$ship->setArmour($armour);
		// Test taking damage
		$result = $ship->takeDamage($damage);
		self::assertSame($expected, $result, $case);
	}

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
					'Shield' => 100,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'Armour' => 100,
					'TotalDamage' => 230,
				],
				100, 10, 100,
			],
			[
				'Do exactly lethal damage (e.g. 230 drone damage)',
				[
					'Shield' => 230,
					'Armour' => 230,
					'Rollover' => true,
				],
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Shield' => 100,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'Armour' => 100,
					'TotalDamage' => 230,
				],
				100, 10, 100,
			],
			[
				'Do NOT do damage to drones behind shields (e.g. armour-only weapon)',
				[
					'Shield' => 0,
					'Armour' => 100,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'Armour' => 0,
					'TotalDamage' => 0,
				],
				100, 10, 100,
			],
			[
				'Do NOT do damage to armour behind shields (e.g. armour-only weapon)',
				[
					'Shield' => 0,
					'Armour' => 100,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'Armour' => 0,
					'TotalDamage' => 0,
				],
				100, 0, 100,
			],
			[
				'Overkill shield damage only (e.g. shield/armour weapon)',
				[
					'Shield' => 150,
					'Armour' => 150,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 100,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'Armour' => 0,
					'TotalDamage' => 100,
				],
				100, 10, 100,
			],
			[
				'Overkill CD damage only (e.g. shield/armour weapon)',
				[
					'Shield' => 150,
					'Armour' => 150,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 30,
					'NumCDs' => 10,
					'HasCDs' => false,
					'Armour' => 0,
					'TotalDamage' => 30,
				],
				0, 10, 100,
			],
			[
				'Overkill armour damage only (e.g. shield/armour weapon)',
				[
					'Shield' => 150,
					'Armour' => 150,
					'Rollover' => false,
				],
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'Armour' => 100,
					'TotalDamage' => 100,
				],
				0, 0, 100,
			],
			[
				'Target is already dead',
				[
					'Shield' => 100,
					'Armour' => 100,
					'Rollover' => true,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => true,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'Armour' => 0,
					'TotalDamage' => 0,
				],
				0, 0, 0,
			],
		];
	}

	/**
	 * @dataProvider dataProvider_takeDamageFromMines
	 */
	public function test_takeDamageFromMines(string $case, int $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a ship with a fixed amount of defenses
		$this->player
			->method('isDead')
			->willReturn($armour == 0);
		$ship = new AbstractSmrShip($this->player);
		$ship->setShields($shields);
		$ship->setCDs($cds);
		$ship->setArmour($armour);
		// Test taking damage from mines
		$damage = [
			'Shield' => $damage,
			'Armour' => $damage,
			'Rollover' => true, // mine damage is always rollover
		];
		$result = $ship->takeDamageFromMines($damage);
		self::assertSame($expected, $result, $case);
	}

	public function dataProvider_takeDamageFromMines(): array {
		return [
			[
				'Do overkill damage (e.g. 1000 mine damage)',
				1000,
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Shield' => 100,
					'CDs' => 0, // No damage to CDs
					'NumCDs' => 0,
					'HasCDs' => true,
					'Armour' => 100,
					'TotalDamage' => 200,
				],
				100, 10, 100,
			],
			[
				'Do exactly lethal damage (e.g. 200 mine damage)',
				200,
				[
					'KillingShot' => true,
					'TargetAlreadyDead' => false,
					'Shield' => 100,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'Armour' => 100,
					'TotalDamage' => 200,
				],
				100, 10, 100,
			],
			[
				'Only do damage to shields',
				20,
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 20,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false, // No CDs to start
					'Armour' => 0,
					'TotalDamage' => 20,
				],
				100, 0, 100,
			],
			[
				'Only do damage to armour (no shields on ship)',
				20,
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => true,
					'Armour' => 20,
					'TotalDamage' => 20,
				],
				0, 10, 100,
			],
			[
				'Target is already dead',
				20,
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => true,
					'Shield' => 0,
					'CDs' => 0,
					'NumCDs' => 0,
					'HasCDs' => false,
					'Armour' => 0,
					'TotalDamage' => 0,
				],
				0, 0, 0,
			],
		];
	}

}
