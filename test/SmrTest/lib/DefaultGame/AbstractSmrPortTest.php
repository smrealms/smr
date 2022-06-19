<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPort;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers AbstractSmrPort
 */
class AbstractSmrPortTest extends TestCase {

	protected function tearDown(): void {
		AbstractSmrPort::clearCache();
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
		$port->addPortGood(GOODS_WOOD, TRADER_SELLS);
		$port->addPortGood(GOODS_ORE, TRADER_BUYS);
		self::assertSame([GOODS_WOOD, GOODS_ORE], $port->getAllGoodIDs());
		self::assertSame([GOODS_WOOD], $port->getSoldGoodIDs());
		self::assertSame([GOODS_ORE], $port->getBoughtGoodIDs());
	}

	/**
	 * @dataProvider provider_removePortGood
	 */
	public function test_removePortGood(array $removeGoodIDs, array $sellRemain, array $buyRemain): void {
		// Set up a port with a couple goods
		$port = AbstractSmrPort::createPort(1, 1);
		$port->addPortGood(GOODS_WOOD, TRADER_SELLS);
		$port->addPortGood(GOODS_ORE, TRADER_BUYS);
		foreach ($removeGoodIDs as $goodID) {
			$port->removePortGood($goodID);
		}
		self::assertSame($sellRemain, $port->getSoldGoodIDs());
		self::assertSame($buyRemain, $port->getBoughtGoodIDs());
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
	public function test_getGoodTransaction(string $transaction): void {
		$port = AbstractSmrPort::createPort(1, 1);
		$port->addPortGood(GOODS_ORE, $transaction);
		self::assertSame($transaction, $port->getGoodTransaction(GOODS_ORE));
	}

	/**
	 * @return array<array<string>>
	 */
	public function provider_getGoodTransaction(): array {
		return [[TRADER_BUYS], [TRADER_SELLS]];
	}

	public function test_getGoodTransaction_throws_if_port_does_not_have_good(): void {
		// New ports don't have any goods yet, so this will throw on any good
		$port = AbstractSmrPort::createPort(1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Port does not trade goodID 3');
		$port->getGoodTransaction(GOODS_ORE);
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

}
