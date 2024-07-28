<?php declare(strict_types=1);

namespace SmrTest\lib\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Path;
use Smr\Port;
use Smr\Routes\MultiplePortRoute;
use Smr\Routes\OneWayRoute;
use Smr\Routes\RouteGenerator;
use Smr\TransactionType;

#[CoversClass(RouteGenerator::class)]
class RouteGeneratorTest extends TestCase {

	private const EMPTY_ROUTES = [
		RouteGenerator::EXP_ROUTE => [],
		RouteGenerator::MONEY_ROUTE => [],
	];

	/**
	 * @param int $raceID
	 * @param array<int, array{TransactionType, int}> $goods
	 */
	private function createPortStub(int $raceID, array $goods): Port {
		// Create a partial mock, only mocking the methods we will use
		$port = $this->createPartialMock(Port::class, ['getGoodDistance', 'getRaceID']);

		// Distances are the most important to mock, since they are complicated
		$getGoodDistanceMap = [];
		foreach ($goods as $goodID => [$_, $distance]) {
			$getGoodDistanceMap[] = [$goodID, $distance];
		}
		$port->method('getGoodDistance')->willReturnMap($getGoodDistanceMap);

		// We could call `setRaceID` instead, but `raceID` is not initialized
		$port->method('getRaceID')->willReturn($raceID);

		// We could mock `hasGood`, but adding the goods is less complicated
		foreach ($goods as $goodID => [$transaction, $_]) {
			$port->addPortGood($goodID, $transaction);
		}

		return $port;
	}

	public function test_one_port_routes(): void {
		// Create a "1-port" route:
		// 1 (Human) buy Nothing for 0x to sell at 2 (Neutral) for 0x (Distance: 1)
		// 2 (Neutral) buy Ore for 1x to sell at 1 (Human) for 1x (Distance: 1)
		$path1 = new Path(1);
		$path1->addLink(2);
		$path2 = clone $path1;
		$path2->reversePath();
		$paths = [1 => [2 => $path1], 2 => [1 => $path2]];

		$port1 = $this->createPortStub(RACE_HUMAN, [GOODS_ORE => [TransactionType::Sell, 1]]);
		$port2 = $this->createPortStub(RACE_NEUTRAL, [GOODS_ORE => [TransactionType::Buy, 1]]);
		$ports = [1 => $port1, 2 => $port2];

		$goods = [GOODS_NOTHING => true, GOODS_ORE => true];
		$races = [RACE_NEUTRAL => true, RACE_HUMAN => true];
		$maxNumPorts = 1;
		$routesForPort = -1;
		$numberOfRoutes = 999;

		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goods, $races, $paths, $routesForPort, $numberOfRoutes);

		// Make sure the output is as expected
		$owr1 = new OneWayRoute(1, 2, RACE_HUMAN, RACE_NEUTRAL, 0, 0, $path1, GOODS_NOTHING);
		$owr2 = new OneWayRoute(2, 1, RACE_NEUTRAL, RACE_HUMAN, 1, 1, $path2, GOODS_ORE);
		$mpr = new MultiplePortRoute($owr1, $owr2);
		$expected = [
			RouteGenerator::EXP_ROUTE => [
				'0.5' => [$mpr],
			],
			RouteGenerator::MONEY_ROUTE => [
				'5.25' => [$mpr],
			],
		];
		self::assertEquals($expected, $routes);

		// If we restrict one of the races, we should get no routes
		$racesRestricted = [RACE_NEUTRAL => true, RACE_HUMAN => false];
		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goods, $racesRestricted, $paths, $routesForPort, $numberOfRoutes);
		self::assertSame(self::EMPTY_ROUTES, $routes);

		// If we restrict one of the goods, we should get no routes
		$goodsRestricted = [GOODS_NOTHING => false, GOODS_ORE => true];
		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goodsRestricted, $races, $paths, $routesForPort, $numberOfRoutes);
		self::assertSame(self::EMPTY_ROUTES, $routes);
	}

	public function test_three_port_routes(): void {
		// Create a 3-port route:
		// 1 (Human) buy Wood for 1x to sell at 2 (Human) for 1x (Distance: 1)
		// 2 (Human) buy Food for 0x to sell at 4 (Thevian) for 0x (Distance: 1)
		// 4 (Thevian) buy Ore for 3x to sell at 1 (Human) for 2x (Distance: 2)
		$path1 = new Path(1);
		$path1->addLink(2);
		$path2 = new Path(2);
		$path2->addLink(3);
		$path3 = new Path(3);
		$path3->addLink(2);
		$path3->addLink(1);
		$paths = [
			1 => [2 => $path1],
			2 => [3 => $path2],
			3 => [1 => $path3],
		];

		$port1 = $this->createPortStub(RACE_HUMAN, [
			GOODS_ORE => [TransactionType::Sell, 2],
			GOODS_WOOD => [TransactionType::Buy, 1],
		]);
		$port2 = $this->createPortStub(RACE_HUMAN, [
			GOODS_WOOD => [TransactionType::Sell, 1],
			GOODS_FOOD => [TransactionType::Buy, 1],
		]);
		$port3 = $this->createPortStub(RACE_THEVIAN, [
			GOODS_FOOD => [TransactionType::Sell, 1],
			GOODS_ORE => [TransactionType::Buy, 2],
		]);
		$ports = [1 => $port1, 2 => $port2, 3 => $port3];

		$goods = [GOODS_FOOD => true, GOODS_ORE => true, GOODS_WOOD => true];
		$races = [RACE_HUMAN => true, RACE_THEVIAN => true];
		$maxNumPorts = 3;
		$routesForPort = -1;
		$numberOfRoutes = 999;

		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goods, $races, $paths, $routesForPort, $numberOfRoutes);

		// Make sure the output is as expected
		$owr1 = new OneWayRoute(1, 2, RACE_HUMAN, RACE_HUMAN, 1, 1, $path1, GOODS_WOOD);
		$owr2 = new OneWayRoute(2, 3, RACE_HUMAN, RACE_THEVIAN, 1, 1, $path2, GOODS_FOOD);
		$owr3 = new OneWayRoute(3, 1, RACE_THEVIAN, RACE_HUMAN, 2, 2, $path3, GOODS_ORE);
		$mpr2 = new MultiplePortRoute($owr1, $owr2);
		$mpr3 = new MultiplePortRoute($mpr2, $owr3);
		$expected = [
			RouteGenerator::EXP_ROUTE => [
				'0.8' => [$mpr3],
			],
			RouteGenerator::MONEY_ROUTE => [
				'7.3' => [$mpr3],
			],
		];
		self::assertEquals($expected, $routes);

		// If we restrict one of the races, we should get no routes
		$racesRestricted = [RACE_HUMAN => false, RACE_THEVIAN => true];
		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goods, $racesRestricted, $paths, $routesForPort, $numberOfRoutes);
		self::assertSame(self::EMPTY_ROUTES, $routes);

		// If we restrict one of the goods, we should get no routes
		$goodsRestricted = [GOODS_FOOD => true, GOODS_ORE => true, GOODS_WOOD => false];
		$routes = RouteGenerator::generateMultiPortRoutes($maxNumPorts, $ports, $goodsRestricted, $races, $paths, $routesForPort, $numberOfRoutes);
		self::assertSame(self::EMPTY_ROUTES, $routes);
	}

}
