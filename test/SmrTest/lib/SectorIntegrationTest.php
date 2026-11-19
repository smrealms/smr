<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Planet;
use Smr\Player;
use Smr\Port;
use Smr\Sector;
use Smr\TransactionType;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Sector::class)]
class SectorIntegrationTest extends BaseIntegrationSpec {

	protected function tearDown(): void {
		Planet::clearCache();
		Port::clearCache();
		Sector::clearCache();
		DiContainer::initialize(false);
	}

	public function test_markVisited(): void {
		$player = $this->getMockBuilder(Player::class)
			->disableOriginalConstructor()
			->onlyMethods(['getPlayerID', 'getAccountID'])
			->getMock();
		$player->expects(self::atLeastOnce())->method('getPlayerID')->willReturn(11);
		$player->expects(self::never())->method('getAccountID')->seal();
		Database::getInstance()->insert('player_visited_sector', [
			'player_id' => 11,
			'game_id' => 42,
			'sector_id' => 1,
		]);
		$sector = Sector::createSector(gameID: 42, sectorID: 1);
		self::assertFalse($sector->isVisited($player));
		$sector->markVisited($player);
		self::assertTrue($sector->isVisited($player));
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
		$player->method('getPlayerID')->willReturn(1);

		// Returns null when no port in sector
		self::assertNull($sector->getCachedPortOrNull($player));

		// Now create a port
		$port = Port::createPort(1, 1);
		$port->setLevel(1); // A new port does not exist until it has been changed.
		$port->addPortGood(GOODS_ORE, TransactionType::Sell);
		$port->addCachePort(1);
		$player = $this->createStub(Player::class);
		$player->method('getPlayerID')->willReturn(1);

		$cachedPort = $sector->getCachedPortOrNull($player);
		self::assertNotNull($cachedPort);
		self::assertSame([GOODS_ORE => TransactionType::Sell], $cachedPort->getGoodTransactions());
	}

	public function test_getPlanetOrNull(): void {
		$sector = Sector::createSector(1, 1);

		// Returns null when no planet in sector
		self::assertNull($sector->getPlanetOrNull());

		// Now create a planet
		$planet = $sector->createPlanet(type: 1, inhabitableTime: 0);

		self::assertSame($planet, $sector->getPlanetOrNull());
	}

}
