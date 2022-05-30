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

}
