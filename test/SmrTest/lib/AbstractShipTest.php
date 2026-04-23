<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Smr\AbstractShip;
use Smr\Bounty;
use Smr\BountyType;
use Smr\Combat\Weapon\Weapon;
use Smr\Globals;
use Smr\Player;
use Smr\Port;
use Smr\ShipClass;
use Smr\ShipIllusion;

/**
 * This test is expected to not make any changes to the database.
 */
#[CoversClass(AbstractShip::class)]
class AbstractShipTest extends TestCase {

	private Player&Stub $player; // will be mocked

	protected function setUp(): void {
		// Create mock player that will be needed to create any ship
		$this->player = $this->createStub(Player::class);
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

	protected function tearDown(): void {
		// Reset Globals::$RACE_RELATIONS due to test_shootPort
		$raceRelations = new ReflectionProperty(Globals::class, 'RACE_RELATIONS');
		$raceRelations->setValue(null, []);
	}

	public function test_base_ship_properties_are_set_correctly(): void {
		$ship = new AbstractShip($this->player);
		self::assertSame('Demonica', $ship->getName());
		self::assertSame(SHIP_TYPE_DEMONICA, $ship->getTypeID());
		self::assertSame(ShipClass::Hunter, $ship->getClass());
		self::assertSame(6, $ship->getHardpoints());
		self::assertSame(10, $ship->getType()->getSpeed());
		self::assertSame(0, $ship->getCost());
	}

	/**
	 * @param array<int> $expectedIDs
	 */
	#[TestWith([0, [1, 2, 0]])] // Moving the top reorders all
	#[TestWith([1, [1, 0, 2]])] // Swap first and second
	#[TestWith([2, [0, 2, 1]])] // Swap second and third
	public function test_moveWeaponUp(int $moveID, array $expectedIDs): void {
		$ship = new AbstractShip($this->player);
		$weapons = [
			Weapon::getWeapon(WEAPON_TYPE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
		];
		foreach ($weapons as $weapon) {
			$ship->addWeapon($weapon);
		}

		$ship->moveWeaponUp($moveID);
		$expected = array_map(fn(int $id) => $weapons[$id], $expectedIDs);
		self::assertSame($expected, $ship->getWeapons());
	}

	/**
	 * @param array<int> $expectedIDs
	 */
	#[TestWith([0, [1, 0, 2]])] // Swap first and second
	#[TestWith([1, [0, 2, 1]])] // Swap second and third
	#[TestWith([2, [2, 0, 1]])] // Moving the bottom reorders all
	public function test_moveWeaponDown(int $moveID, array $expectedIDs): void {
		$ship = new AbstractShip($this->player);
		$weapons = [
			Weapon::getWeapon(WEAPON_TYPE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
		];
		foreach ($weapons as $weapon) {
			$ship->addWeapon($weapon);
		}

		$ship->moveWeaponDown($moveID);
		$expected = array_map(fn(int $id) => $weapons[$id], $expectedIDs);
		self::assertSame($expected, $ship->getWeapons());
	}

	public function test_moveWeaponUp_no_weapons(): void {
		$ship = new AbstractShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This method cannot be used when there are no weapons');
		$ship->moveWeaponUp(0);
	}

	public function test_moveWeaponDown_no_weapons(): void {
		$ship = new AbstractShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This method cannot be used when there are no weapons');
		$ship->moveWeaponDown(0);
	}

	public function test_cloak(): void {
		$ship = new AbstractShip($this->player);

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
		$ship = new AbstractShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->enableCloak();
	}

	public function test_illusion_generator(): void {
		$ship = new AbstractShip($this->player);

		// ship has no IG initially
		self::assertFalse($ship->getIllusion());

		// remain unset when disabled without hardware
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusion());

		// add IG hardware
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertFalse($ship->getIllusion());
		// enable
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
		$expected = new ShipIllusion(
			shipTypeID: SHIP_TYPE_THIEF,
			attackRating: 12,
			defenseRating: 13,
		);
		self::assertEquals($expected, $ship->getIllusion());
		// disable
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusion());
	}

	public function test_illusion_throws_when_missing_hardware(): void {
		$ship = new AbstractShip($this->player);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
	}

	public function test_hardware(): void {
		$ship = new AbstractShip($this->player);

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
	 * @param WeaponDamageData $damage
	 * @param TakenDamageData $expected
	 */
	#[DataProvider('dataProvider_takeDamage')]
	public function test_takeDamage(string $case, array $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a ship with a fixed amount of defenses
		$this->player
			->method('isDead')
			->willReturn($armour === 0);
		$ship = new AbstractShip($this->player);
		$ship->setShields($shields);
		$ship->setCDs($cds);
		$ship->setArmour($armour);
		// Test taking damage
		$result = $ship->takeDamage($damage);
		self::assertSame($expected, $result, $case);
	}

	/**
	 * @return array<array{0: string, 1: WeaponDamageData, 2: TakenDamageData, 3: int, 4: int, 5: int}>
	 */
	public static function dataProvider_takeDamage(): array {
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
	 * @param TakenDamageData $expected
	 */
	#[DataProvider('dataProvider_takeDamageFromMines')]
	public function test_takeDamageFromMines(string $case, int $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a ship with a fixed amount of defenses
		$this->player
			->method('isDead')
			->willReturn($armour === 0);
		$ship = new AbstractShip($this->player);
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

	/**
	 * @return array<array{0: string, 1: int, 2: TakenDamageData, 3: int, 4: int, 5: int}>
	 */
	public static function dataProvider_takeDamageFromMines(): array {
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

	public function test_shootPort(): void {
		$bounty = $this->createMock(Bounty::class);
		$bounty
			->expects(self::once())
			->method('increaseCredits')
			->with(44_800)
			->seal();

		$gameID = 3;
		$player = $this->createMock(Player::class);
		$player
			->expects(self::atLeastOnce())
			->method('getAccountID')
			->willReturn(999); // value doesn't matter
		$player
			->expects(self::atLeastOnce())
			->method('getGameID')
			->willReturn($gameID);
		$player
			->expects(self::atLeastOnce())
			->method('getRaceID')
			->willReturn(RACE_THEVIAN);
		$player
			->expects(self::once())
			->method('getShipTypeID')
			->willReturn(SHIP_TYPE_ASSAULT_CRAFT);
		$player
			->expects(self::atLeastOnce())
			->method('getLevelID')
			->willReturn(25);
		$player
			->expects(self::once())
			->method('isDead')
			->willReturn(false);
		$player
			->expects(self::once())
			->method('getActiveBounty')
			->with(BountyType::HQ)
			->willReturn($bounty);
		$player
			->expects(self::once())
			->method('increaseExperience')
			->with(84);
		$increaseHof = [];
		$player
			->method('increaseHOF')
			->willReturnCallback(function (...$args) use (&$increaseHof) {
				$increaseHof[] = $args;
			});
		$decreaseRelations = [];
		$player
			->method('decreaseRelations')
			->willReturnCallback(function (...$args) use (&$decreaseRelations) {
				$decreaseRelations[] = $args;
			});
		$increaseRelations = [];
		$player
			->method('increaseRelations')
			->willReturnCallback(function (...$args) use (&$increaseRelations) {
				$increaseRelations[] = $args;
			});
		$player
			->expects(self::once())
			->method('increaseAlignment')
			->with(1)
			->seal();

		$port = Port::createPort($gameID, 9);
		$port->setRaceID(RACE_SALVENE);
		$port->upgradeToLevel(2);
		$port->checkDefenses(); // populates shields/armour/cds

		// Set Globals::$RACE_RELATIONS
		$raceRelations = new ReflectionProperty(Globals::class, 'RACE_RELATIONS');
		$raceRelations->setValue(null, [
			$gameID => [
				RACE_SALVENE => [
					RACE_THEVIAN => -500,
					RACE_SALVENE => 500,
					RACE_NIJARIN => 350,
					RACE_ALSKANT => -250,
				],
			],
		]);

		$ship = new AbstractShip($player);
		$weapons = [
			Weapon::getWeapon(WEAPON_TYPE_HHG),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
		];
		foreach ($weapons as $weapon) {
			$ship->addWeapon($weapon);
		}

		srand(16); // set rand seed for weapons
		$result = $ship->shootPort($port);

		// Validate the array returned from shootPort
		$hhgDamage = [
			'Shield' => 300,
			'Armour' => 0,
			'Rollover' => false,
		];
		$hplDamage = [
			'Shield' => 80,
			'Armour' => 80,
			'Rollover' => false,
		];
		$lplDamage = [
			'Shield' => 60,
			'Armour' => 60,
			'Rollover' => false,
		];
		$getActualDamage = function (int $damage): array {
			return [
				'KillingShot' => false,
				'TargetAlreadyDead' => false,
				'Shield' => $damage,
				'CDs' => 0,
				'NumCDs' => 0,
				'HasCDs' => true,
				'Armour' => 0,
				'TotalDamage' => $damage,
			];
		};
		$expected = [
			'Player' => $player,
			'TotalDamage' => 560,
			'Weapons' => [
				[
					'Weapon' => $weapons[0],
					'Target' => $port,
					'Hit' => true,
					'WeaponDamage' => $hhgDamage,
					'ActualDamage' => $getActualDamage(300),
				],
				[
					'Weapon' => $weapons[1],
					'Target' => $port,
					'Hit' => true,
					'WeaponDamage' => $hplDamage,
					'ActualDamage' => $getActualDamage(80),
				],
				[
					'Weapon' => $weapons[2],
					'Target' => $port,
					'Hit' => false,
				],
				[
					'Weapon' => $weapons[3],
					'Target' => $port,
					'Hit' => true,
					'WeaponDamage' => $lplDamage,
					'ActualDamage' => $getActualDamage(60),
				],
				[
					'Weapon' => $weapons[4],
					'Target' => $port,
					'Hit' => true,
					'WeaponDamage' => $lplDamage,
					'ActualDamage' => $getActualDamage(60),
				],
				[
					'Weapon' => $weapons[5],
					'Target' => $port,
					'Hit' => true,
					'WeaponDamage' => $lplDamage,
					'ActualDamage' => $getActualDamage(60),
				],
			],
			'DeadBeforeShot' => false,
		];
		self::assertSame($expected, $result);

		// Validate the mocked functions called multiple times in shootPort
		$decreaseRelationsExpected = [
			[2, RACE_SALVENE],
			[1, RACE_NIJARIN],
			[0, RACE_ALSKANT],
		];
		self::assertSame($decreaseRelationsExpected, $decreaseRelations);

		$increaseRelationsExpected = [
			[2, RACE_THEVIAN],
		];
		self::assertSame($increaseRelationsExpected, $increaseRelations);

		$increaseHofExpected = [
			[560., ['Combat', 'Port', 'Damage Done'], HOF_PUBLIC],
			[44_800., ['Combat', 'Port', 'Bounties', 'Gained'], HOF_PUBLIC],
			[2., ['Combat', 'Port', 'Relation', 'Gain'], HOF_PUBLIC],
			[2., ['Combat', 'Port', 'Relation', 'Loss'], HOF_PUBLIC],
			[1., ['Combat', 'Port', 'Relation', 'Loss'], HOF_PUBLIC],
			[0., ['Combat', 'Port', 'Relation', 'Loss'], HOF_PUBLIC],
			[1., ['Combat', 'Port', 'Alignment', 'Gain'], HOF_PUBLIC],
		];
		self::assertSame($increaseHofExpected, $increaseHof);

	}

}
