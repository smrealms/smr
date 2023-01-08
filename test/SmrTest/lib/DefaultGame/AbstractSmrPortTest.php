<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPort;
use Exception;
use PHPUnit\Framework\TestCase;
use Smr\Bounty;
use Smr\BountyType;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\TransactionType;
use SmrGame;
use SmrPlayer;
use SmrSector;

/**
 * @covers AbstractSmrPort
 */
class AbstractSmrPortTest extends TestCase {

	protected function tearDown(): void {
		AbstractSmrPort::clearCache();
		DiContainer::initialize(false);
	}

	public static function tearDownAfterClass(): void {
		SmrSector::clearCache();
		SmrGame::clearCache();
	}

	public function test_new_port_does_not_exist_yet(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		self::assertFalse($port->exists());
	}

	public function test_port_primary_keys(): void {
		// Ports are keyed on game ID and sector ID
		$gameID = 2;
		$sectorID = 3;
		$port = AbstractSmrPort::createPort($gameID, $sectorID);
		self::assertSame($gameID, $port->getGameID());
		self::assertSame($sectorID, $port->getSectorID());
	}

	public function test_setRaceID(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		// New ports start as Neutral
		self::assertSame(RACE_NEUTRAL, $port->getRaceID());
		// Then check changing the race
		$port->setRaceID(RACE_HUMAN);
		self::assertSame(RACE_HUMAN, $port->getRaceID());
	}

	public function test_addPortGood(): void {
		// When we add the good
		$port = AbstractSmrPort::createPort(1, 1);
		$port->addPortGood(GOODS_ORE, TransactionType::Buy);
		// Insert smaller ID port good second to test sorting
		$port->addPortGood(GOODS_WOOD, TransactionType::Sell);
		self::assertSame([GOODS_WOOD, GOODS_ORE], $port->getAllGoodIDs());
		self::assertSame([GOODS_WOOD], $port->getSellGoodIDs());
		self::assertSame([GOODS_ORE], $port->getBuyGoodIDs());
	}

	/**
	 * @dataProvider provider_removePortGood
	 *
	 * @param array<int> $removeGoodIDs
	 * @param array<int> $sellRemain
	 * @param array<int> $buyRemain
	 */
	public function test_removePortGood(array $removeGoodIDs, array $sellRemain, array $buyRemain): void {
		// Set up a port with a couple goods
		$port = AbstractSmrPort::createPort(1, 1);
		$port->addPortGood(GOODS_WOOD, TransactionType::Sell);
		$port->addPortGood(GOODS_ORE, TransactionType::Buy);
		foreach ($removeGoodIDs as $goodID) {
			$port->removePortGood($goodID);
		}
		self::assertSame($sellRemain, $port->getSellGoodIDs());
		self::assertSame($buyRemain, $port->getBuyGoodIDs());
		self::assertSame(array_merge($sellRemain, $buyRemain), $port->getAllGoodIDs());
	}

	/**
	 * @return array<array<array<int>>>
	 */
	public function provider_removePortGood(): array {
		return [
			// Remove a good that the port doesn't have
			[[GOODS_CIRCUITRY], [GOODS_WOOD], [GOODS_ORE]],
			// Remove a buyable good
			[[GOODS_WOOD], [], [GOODS_ORE]],
			// Remove a sellable good
			[[GOODS_ORE], [GOODS_WOOD], []],
			// Remove both goods
			[[GOODS_WOOD, GOODS_ORE], [], []],
		];
	}

	/**
	 * @dataProvider provider_getGoodTransaction
	 */
	public function test_getGoodTransaction(TransactionType $transaction): void {
		$port = AbstractSmrPort::createPort(1, 1);
		$port->addPortGood(GOODS_ORE, $transaction);
		self::assertSame($transaction, $port->getGoodTransaction(GOODS_ORE));
	}

	/**
	 * @return array<array<TransactionType>>
	 */
	public function provider_getGoodTransaction(): array {
		return [[TransactionType::Buy], [TransactionType::Sell]];
	}

	public function test_getGoodTransaction_throws_if_port_does_not_have_good(): void {
		// New ports don't have any goods yet, so this will throw on any good
		$port = AbstractSmrPort::createPort(1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Port does not trade goodID 3');
		$port->getGoodTransaction(GOODS_ORE);
	}

	public function test_setPortGoods(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		$port->setLevel(1);

		// By default, a port's goods are empty
		self::assertSame([], $port->getGoodTransactions());

		// If we try to add an out-of-order good, we don't modify the port
		// and we return false.
		$badGoods = [
			GOODS_TEXTILES => TransactionType::Buy,
		];
		self::assertFalse($port->setPortGoods($badGoods));
		self::assertSame([], $port->getGoodTransactions());

		// If we add valid goods, the port is properly modified
		// (A level 1 port requires 3 goods)
		$validGoods = [
			GOODS_WOOD => TransactionType::Buy,
			GOODS_FOOD => TransactionType::Buy,
			GOODS_ORE => TransactionType::Sell,
		];
		self::assertTrue($port->setPortGoods($validGoods));
		self::assertSame($validGoods, $port->getGoodTransactions());

		// If we specify new goods, they are completely overriden
		$validGoods2 = [
			GOODS_WOOD => TransactionType::Sell, // opposite transaction
			GOODS_FOOD => TransactionType::Buy, // same transaction
			GOODS_SLAVES => TransactionType::Buy, // different good
		];
		self::assertTrue($port->setPortGoods($validGoods2));
		self::assertSame($validGoods2, $port->getGoodTransactions());
	}

	public function test_shields(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		// newly created ports start with no shields
		self::assertSame(0, $port->getShields());
		self::assertFalse($port->hasShields());

		// Test setting shields explicitly
		$port->setShields(100);
		self::assertSame(100, $port->getShields());
		self::assertTrue($port->hasShields());

		// Test decreasing shields
		$port->decreaseShields(2);
		self::assertSame(98, $port->getShields());
	}

	public function test_cds(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		// newly created ports start with no CDs
		self::assertSame(0, $port->getCDs());
		self::assertFalse($port->hasCDs());

		// Test setting CDs explicitly
		$port->setCDs(100);
		self::assertSame(100, $port->getCDs());
		self::assertTrue($port->hasCDs());

		// Test decreasing CDs
		$port->decreaseCDs(2);
		self::assertSame(98, $port->getCDs());
	}

	public function test_armour(): void {
		$port = AbstractSmrPort::createPort(1, 1);
		// newly created ports start with no armour
		self::assertSame(0, $port->getArmour());
		self::assertFalse($port->hasArmour());

		// Test setting shields explicitly
		$port->setArmour(100);
		self::assertSame(100, $port->getArmour());
		self::assertTrue($port->hasArmour());

		// Test decreasing shields
		$port->decreaseArmour(2);
		self::assertSame(98, $port->getArmour());
	}

	/**
	 * @dataProvider dataProvider_takeDamage
	 *
	 * @param WeaponDamageData $damage
	 * @param array<string, int|bool> $expected
	 */
	public function test_takeDamage(string $case, array $damage, array $expected, int $shields, int $cds, int $armour): void {
		// Set up a port with a fixed amount of defenses
		$port = AbstractSmrPort::createPort(1, 1);
		$port->setShields($shields);
		$port->setCDs($cds);
		$port->setArmour($armour);
		// Test taking damage
		$result = $port->takeDamage($damage);
		self::assertSame($expected, $result, $case);
	}

	/**
	 * @return array<array{0: string, 1: WeaponDamageData, 2: array<string, int|bool>, 3: int, 4: int, 5: int}>
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

	/**
	 * Ensure that the state of the port is self-consistent when it is
	 * destroyed and loses a level in the same attack.
	 */
	public function test_port_loses_level_on_raid_killshot(): void {
		// We're not testing database modifications, so stub it
		$db = $this->createStub(Database::class);
		DiContainer::getContainer()->set(Database::class, $db);

		// Add a few basic checks on the player that gets the killshot
		$portLevel = 3;
		$playerExp = 100;

		$bounty = $this->createMock(Bounty::class);
		$bounty
			->expects(self::once())
			->method('increaseCredits')
			->with($portLevel * $playerExp);
		$player = $this->createMock(SmrPlayer::class);
		$player
			->expects(self::once())
			->method('decreaseRelations')
			->with(AbstractSmrPort::KILLER_RELATIONS_LOSS, RACE_NEUTRAL);
		$player
			->expects(self::once())
			->method('getActiveBounty')
			->with(BountyType::HQ)
			->willReturn($bounty);
		$player
			->method('getExperience')
			->willReturn($playerExp);

		// Make objects that must be accessed statically (can't be mocked)
		SmrSector::createSector(1, 1);
		SmrGame::createGame(1)->setGameTypeID(SmrGame::GAME_TYPE_DEFAULT);

		// Set up the port
		$port = AbstractSmrPort::createPort(1, 1);
		$port->upgradeToLevel($portLevel);

		// Imitate the scenario of de-leveling a port in the same attack that
		// destroys the port. While there's a lot we could verify here, most
		// important is to make sure that it doesn't throw.
		$result = $port->killPortByPlayer($player);
		$port->upgradeToLevel($portLevel - 1);
		$port->update();

		// killPortByPlayer should always return an empty array
		self::assertSame([], $result);
	}

}
