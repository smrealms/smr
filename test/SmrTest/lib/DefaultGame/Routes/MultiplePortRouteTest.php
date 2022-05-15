<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame\Routes;

use PHPUnit\Framework\TestCase;
use Smr\Path;
use Smr\Routes\MultiplePortRoute;
use Smr\Routes\OneWayRoute;

/**
 * @covers Smr\Routes\MultiplePortRoute
 * @covers Smr\Routes\Route
 */
class MultiplePortRouteTest extends TestCase {

	public function test_three_port_routes(): void {
		// Create a 3-port route from 1->2, 2->4, 4->1
		$path1 = new Path(1);
		$path1->addLink(2);
		$route1 = new OneWayRoute(1, 2, RACE_NEUTRAL, RACE_HUMAN, 1, 1, $path1, GOODS_WOOD);
		$path2 = new Path(2);
		$path2->addLink(3);
		$path2->addLink(4);
		$route2 = new OneWayRoute(2, 4, RACE_HUMAN, RACE_THEVIAN, 0, 0, $path2, GOODS_NOTHING);
		$path3 = new Path(4);
		$path3->addLink(3);
		$path3->addLink(2);
		$path3->addLink(1);
		$route3 = new OneWayRoute(4, 1, RACE_THEVIAN, RACE_NEUTRAL, 3, 2, $path3, GOODS_ORE);
		$mpr1 = new MultiplePortRoute($route1, $route2);
		$mpr = new MultiplePortRoute($mpr1, $route3);

		// Make sure it only contains the ports in the route
		self::assertTrue($mpr->containsPort(1));
		self::assertTrue($mpr->containsPort(2));
		self::assertFalse($mpr->containsPort(3));
		self::assertTrue($mpr->containsPort(4));
		self::assertFalse($mpr->containsPort(5));
		self::assertSame([1, 2, 4], $mpr->getPortSectorIDs());

		// Make sure the forward and return routes are correct
		self::assertSame($mpr1, $mpr->getForwardRoute());
		self::assertSame($route3, $mpr->getReturnRoute());

		// Make sure the route statistics are correct
		self::assertSame(10, $mpr->getTurnsForRoute());
		self::assertSame(0.7, $mpr->getOverallExpMultiplier());
		self::assertSame(5.9, $mpr->getOverallMoneyMultiplier());

		// Make sure the route string is in the right order
		$expected = implode("\n", [
			$route1->getRouteString(),
			$route2->getRouteString(),
			$route3->getRouteString(),
		]);
		self::assertSame($expected, $mpr->getRouteString());
	}

}
