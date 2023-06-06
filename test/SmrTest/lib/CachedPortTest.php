<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Container\DiContainer;
use Smr\Epoch;
use Smr\Port;
use Smr\TransactionType;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Port::class)]
class CachedPortTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['player_visited_port', 'port_info_cache'];
	}

	protected function tearDown(): void {
		Port::clearCache();
		// Reset the DI container to avoid contaminating other tests
		DiContainer::initialize(false);
	}

	public function test_addCachePorts(): void {
		$account1 = 42;
		$account2 = 43;

		// Create an arbitrary Port
		$sectorID = 2;
		$gameID = 3;
		$port1 = Port::createPort($gameID, $sectorID);
		$port1->setCredits(1); // so that exists() is true
		$port1->addPortGood(GOODS_ORE, TransactionType::Sell);

		// Add a cached port from a Port
		$added = $port1->addCachePorts([$account1]);
		self::assertTrue($added);

		// Check that the goods on the cached Port match the original
		$cachedPort1 = Port::getCachedPort($gameID, $sectorID, $account1);
		$expected = [GOODS_ORE => TransactionType::Sell];
		self::assertSame($expected, $cachedPort1->getGoodTransactions());

		// Add a cached port from a cached Port (test multiple accounts at once)
		$added = $cachedPort1->addCachePorts([$account1, $account2]);
		self::assertTrue($added);

		// Create a "newer" version of the Port with a larger cache time
		$t2 = Epoch::time() + 2;
		$epoch = $this->createStub(Epoch::class);
		$epoch->method('getTime')->willReturn($t2);
		DiContainer::getContainer()->set(Epoch::class, $epoch);

		Port::clearCache();
		$port2 = Port::createPort($gameID, $sectorID);
		$port2->setCredits(1); // so that exists() is true
		self::assertSame($t2, $port2->getCachedTime());

		// Adding with a newer Port does change the cache
		$port2->addPortGood(GOODS_WOOD, TransactionType::Buy);
		$port2->addCachePort($account2);
		$cachedPort2 = Port::getCachedPort($gameID, $sectorID, $account2);
		$expected = [GOODS_WOOD => TransactionType::Buy];
		self::assertSame($expected, $cachedPort2->getGoodTransactions());

		// Adding with an older Port does not change the cache
		$port1->addCachePort($account2);
		$cachedPort2 = Port::getCachedPort($gameID, $sectorID, $account2, true);
		self::assertSame($expected, $cachedPort2->getGoodTransactions());
	}

}
