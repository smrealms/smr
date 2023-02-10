<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Smr\Planet;
use Smr\TradeGood;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Planet::class)]
class PlanetIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['planet'];
	}

	protected function tearDown(): void {
		Planet::clearCache();
	}

	public function test_createPlanet(): void {
		// Test arbitrary input
		$sectorID = 2;
		$gameID = 42;
		$typeID = 3;
		$inhabitableTime = 5;

		$planet = Planet::createPlanet($gameID, $sectorID, $typeID, $inhabitableTime);
		$this->assertTrue($planet->exists());

		// Check properties set explicitly
		$this->assertSame($gameID, $planet->getGameID());
		$this->assertSame($sectorID, $planet->getSectorID());
		$this->assertSame($typeID, $planet->getTypeID());
		$this->assertSame($inhabitableTime, $planet->getInhabitableTime());
	}

	public function test_createPlanet_already_exists(): void {
		Planet::createPlanet(1, 1, 1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Planet already exists');
		Planet::createPlanet(1, 1, 1, 1);
	}

	public function test_removePlanet(): void {
		// Check that planet exists
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertTrue($planet->exists());

		Planet::removePlanet(1, 1);
		$planet = Planet::getPlanet(1, 1, true);
		$this->assertFalse($planet->exists());
	}

	public function test_name(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);
		// Check default name
		$this->assertSame('Unknown', $planet->getDisplayName());

		// Set a new name (include non-HTML-safe character)
		$planet->setName('Test&');
		$this->assertSame('Test&amp;', $planet->getDisplayName());
	}

	public function test_owner(): void {
		// Check default owner
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertFalse($planet->hasOwner());
		$this->assertSame(0, $planet->getOwnerID());

		// Set a new owner
		$ownerID = 3;
		$planet->setOwnerID($ownerID);
		$this->assertTrue($planet->hasOwner());
		$this->assertSame($ownerID, $planet->getOwnerID());

		// Remove the owner again
		$planet->removeOwner();
		$this->assertFalse($planet->hasOwner());
		$this->assertSame(0, $planet->getOwnerID());
	}

	public function test_password(): void {
		// Check default password
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertSame('', $planet->getPassword());

		// Set a new password
		$password = 'test';
		$planet->setPassword($password);
		$this->assertSame($password, $planet->getPassword());

		// Remove the password again
		$planet->removePassword();
		$this->assertSame('', $planet->getPassword());
	}

	public function test_credits(): void {
		// Check default credits
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getCredits());

		// Check increase/decrease credits
		$planet->increaseCredits(100);
		$this->assertSame(100, $planet->getCredits());
		$planet->increaseCredits(50);
		$this->assertSame(150, $planet->getCredits());
		$planet->decreaseCredits(50);
		$this->assertSame(100, $planet->getCredits());
	}

	public function test_bonds(): void {
		// Check default bond
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getBonds());

		// Check increase/decrease bonds
		$planet->increaseBonds(100);
		$this->assertSame(100, $planet->getBonds());
		$planet->increaseBonds(50);
		$this->assertSame(150, $planet->getBonds());
		$planet->decreaseBonds(50);
		$this->assertSame(100, $planet->getBonds());
	}

	public function test_bond_maturity(): void {
		// Check default maturity
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getMaturity());

		// Set a new bond maturity
		$maturity = time();
		$planet->setMaturity($maturity);
		$this->assertSame($maturity, $planet->getMaturity());
	}

	public function test_stockpile(): void {
		// Check default stockpile
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->assertFalse($planet->hasStockpile());
		$this->assertSame([], $planet->getStockpile());
		foreach (TradeGood::getAllIDs() as $goodID) {
			$this->assertFalse($planet->hasStockpile($goodID));
			$this->assertSame(0, $planet->getStockpile($goodID));
		}

		// Setting 0 still counts as empty
		$planet->setStockpile(GOODS_ORE, 0);
		$this->assertFalse($planet->hasStockpile());
		$this->assertFalse($planet->hasStockpile(GOODS_ORE));

		// Check increase stockpile
		$planet->increaseStockpile(GOODS_ORE, 50);
		$this->assertTrue($planet->hasStockpile());
		$this->assertSame([GOODS_ORE => 50], $planet->getStockpile());
		foreach (TradeGood::getAllIDs() as $goodID) {
			if ($goodID === GOODS_ORE) {
				$this->assertTrue($planet->hasStockpile($goodID));
				$this->assertSame(50, $planet->getStockpile($goodID));
			} else {
				$this->assertFalse($planet->hasStockpile($goodID));
				$this->assertSame(0, $planet->getStockpile($goodID));
			}
		}

		// Check decrease stockpile
		$planet->decreaseStockpile(GOODS_ORE, 10);
		$this->assertTrue($planet->hasStockpile());
		$this->assertSame([GOODS_ORE => 40], $planet->getStockpile());
		foreach (TradeGood::getAllIDs() as $goodID) {
			if ($goodID === GOODS_ORE) {
				$this->assertTrue($planet->hasStockpile($goodID));
				$this->assertSame(40, $planet->getStockpile($goodID));
			} else {
				$this->assertFalse($planet->hasStockpile($goodID));
				$this->assertSame(0, $planet->getStockpile($goodID));
			}
		}

		// Check remaining stockpile (ore: 600 - 40)
		$this->assertSame(560, $planet->getRemainingStockpile(GOODS_ORE));
	}

	public function test_setStockpile_throws_when_negative(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Trying to set negative stockpile');
		$planet->setStockpile(GOODS_ORE, -20);
	}

	public function test_setBuilding_throws_when_negative(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot set negative number of buildings');
		$planet->setBuilding(PLANET_HANGAR, -1);
	}

	public function test_destroyBuilding_throws_when_invalid(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot set negative number of buildings');
		$planet->destroyBuilding(PLANET_TURRET, 1);
	}

	public function test_checkForDowngrade(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);

		// If we don't do enough damage, we should never downgrade
		$this->assertSame([], $planet->checkForDowngrade(0));

		// With no buildings, this should always return empty
		$this->assertSame([], $planet->checkForDowngrade(100 * Planet::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE));

		// Give the planet 2 structures, and destroy them both
		$planet->setBuilding(PLANET_GENERATOR, 2);
		srand(95); // seed rand for reproducibility
		$result = $planet->checkForDowngrade(2 * Planet::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		$this->assertSame([PLANET_GENERATOR => 2], $result);
	}

	public function test_buildings(): void {
		$planet = Planet::createPlanet(1, 1, 1, 1);

		// Tests with no buildings
		$this->assertFalse($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(0, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(0.0, $planet->getLevel());

		// Add some hangars
		$planet->increaseBuilding(PLANET_HANGAR, 4);
		$this->assertTrue($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(4, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(4 / 3, $planet->getLevel());

		// Destroy some hangars
		$planet->destroyBuilding(PLANET_HANGAR, 2);
		$this->assertTrue($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(2, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(2 / 3, $planet->getLevel());
	}

	public function test_defenses(): void {
		// Make a Defense World planet
		$planet = Planet::createPlanet(1, 1, 4, 1);

		// Add buildings so that we can add defenses
		$planet->increaseBuilding(PLANET_GENERATOR, 1);
		$planet->increaseBuilding(PLANET_HANGAR, 1);
		$planet->increaseBuilding(PLANET_BUNKER, 1);

		// Make sure there are no defenses to start
		self::assertSame(0, $planet->getShields());
		self::assertFalse($planet->hasShields());
		self::assertSame(0, $planet->getCDs());
		self::assertFalse($planet->hasCDs());
		self::assertSame(0, $planet->getArmour());
		self::assertFalse($planet->hasArmour());

		// Increase shields
		$planet->increaseShields(10);
		self::assertSame(10, $planet->getShields());
		self::assertTrue($planet->hasShields());

		// Don't increase shields
		$planet->increaseShields(0);
		self::assertSame(10, $planet->getShields());

		// Decrease shields
		$planet->decreaseShields(2);
		self::assertSame(8, $planet->getShields());

		// Make sure we can't go above the Generator limit
		$planet->setShields(PLANET_GENERATOR_SHIELDS + 1);
		self::assertSame(PLANET_GENERATOR_SHIELDS, $planet->getShields());

		// Make sure we can't go below 0 shields
		$planet->setShields(-1);
		self::assertSame(0, $planet->getShields());

		// Increase CDs
		$planet->increaseCDs(5);
		self::assertSame(5, $planet->getCDs());
		self::assertTrue($planet->hasCDs());

		// Don't increase CDs
		$planet->increaseCDs(0);
		self::assertSame(5, $planet->getCDs());

		// Decrease CDs
		$planet->decreaseCDs(3);
		self::assertSame(2, $planet->getCDs());

		// Make sure we can't go above the Hangar limit
		$planet->setCDs(PLANET_HANGAR_DRONES + 1);
		self::assertSame(PLANET_HANGAR_DRONES, $planet->getCDs());

		// Make sure we can't go below 0 CDs
		$planet->setCDs(-1);
		self::assertSame(0, $planet->getCDs());

		// Increase armour
		$planet->increaseArmour(15);
		self::assertSame(15, $planet->getArmour());
		self::assertTrue($planet->hasArmour());

		// Don't increase armour
		$planet->increaseArmour(0);
		self::assertSame(15, $planet->getArmour());

		// Decrease armour
		$planet->decreaseArmour(4);
		self::assertSame(11, $planet->getArmour());

		// Make sure we can't go above the Bunker limit
		$planet->setArmour(PLANET_BUNKER_ARMOUR + 1);
		self::assertSame(PLANET_BUNKER_ARMOUR, $planet->getArmour());

		// Make sure we can't go below 0 armour
		$planet->setArmour(-1);
		self::assertSame(0, $planet->getArmour());
	}

	/**
	 * @param int $planetType
	 * @param array<int, int> $expected
	 */
	#[DataProvider('provider_getMaxBuildings')]
	public function test_getMaxBuildings(int $planetType, array $expected): void {
		$planet = Planet::createPlanet(1, 1, $planetType, 1);
		self::assertSame($expected, $planet->getMaxBuildings());
	}

	/**
	 * @return array<array{int, array<int, int>}>
	 */
	public static function provider_getMaxBuildings(): array {
		return [
			[
				1,
				[
					PLANET_GENERATOR => 25,
					PLANET_HANGAR => 100,
					PLANET_TURRET => 10,
				],
			],
			[
				2,
				[
					PLANET_GENERATOR => 25,
					PLANET_BUNKER => 25,
					PLANET_TURRET => 15,
				],
			],
		];
	}

	/**
	 * @param WeaponDamageData $damage
	 * @param TakenDamageData $expected
	 */
	#[DataProvider('dataProvider_takeDamage')]
	public function test_takeDamage(string $case, array $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a port with a fixed amount of defenses
		$planet = Planet::createPlanet(1, 1, 4, 1);
		$planet->setBuilding(PLANET_GENERATOR, 1);
		$planet->setBuilding(PLANET_HANGAR, 2);
		$planet->setBuilding(PLANET_BUNKER, 1);
		$planet->setShields($shields);
		$planet->setCDs($cds);
		$planet->setArmour($armour);
		// Test taking damage
		$result = $planet->takeDamage($damage);
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
				'Do damage to drones behind shields (e.g. armour-only weapon)',
				[
					'Shield' => 0,
					'Armour' => 100,
					'Rollover' => false,
				],
				[
					'KillingShot' => false,
					'TargetAlreadyDead' => false,
					'Shield' => 0,
					'CDs' => 18,
					'NumCDs' => 6,
					'HasCDs' => true,
					'Armour' => 0,
					'TotalDamage' => 18,
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

}
