<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Container\DiContainer;
use Smr\Player;
use Smr\Port;
use Smr\Sector;
use Smr\TransactionType;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Sector::class)]
class SectorIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['player_visited_port', 'port_info_cache'];
	}

	protected function tearDown(): void {
		Port::clearCache();
		Sector::clearCache();
		DiContainer::initialize(false);
	}

	public function test_getPortOrNull(): void {
		$sector = Sector::createSector(1, 1);

		// Returns null when no port in sector
		self::assertNull($sector->getPortOrNull());

		// Now create a port
		$port = Port::createPort(1, 1);
		$port->setLevel(1); // A new port does not exist until it has been changed.

		self::assertSame($port, $sector->getPortOrNull());
	}

	public function test_getCachedPortOrNull(): void {
		$sector = Sector::createSector(1, 1);
		$player = $this->createStub(Player::class);
		$player->method('getAccountID')->willReturn(1);

		// Returns null when no port in sector
		self::assertNull($sector->getCachedPortOrNull($player));

		// Now create a port
		$port = Port::createPort(1, 1);
		$port->setLevel(1); // A new port does not exist until it has been changed.
		$port->addPortGood(GOODS_ORE, TransactionType::Sell);
		$port->addCachePort(1);
		$player = $this->createStub(Player::class);
		$player->method('getAccountID')->willReturn(1);

		$cachedPort = $sector->getCachedPortOrNull($player);
		self::assertNotNull($cachedPort);
		self::assertSame([GOODS_ORE => TransactionType::Sell], $cachedPort->getGoodTransactions());
	}

}
