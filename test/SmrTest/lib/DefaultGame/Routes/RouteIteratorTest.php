<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame\Routes;

use PHPUnit\Framework\TestCase;
use Smr\Path;
use Smr\Routes\MultiplePortRoute;
use Smr\Routes\OneWayRoute;
use Smr\Routes\RouteIterator;
use Smr\TransactionType;

/**
 * @covers Smr\Routes\RouteIterator
 */
class RouteIteratorTest extends TestCase {

	public function test_iterator_states(): void {
		// Create a 2-port route from 1->2, 2->1
		$path1 = new Path(1);
		$path1->addLink(2);
		$route1 = new OneWayRoute(1, 2, RACE_HUMAN, RACE_HUMAN, 1, 1, $path1, GOODS_WOOD);
		$path2 = new Path(2);
		$path2->addLink(1);
		$route2 = new OneWayRoute(2, 1, RACE_HUMAN, RACE_HUMAN, 1, 1, $path2, GOODS_ORE);
		$mpr = new MultiplePortRoute($route1, $route2);

		$iterator = new RouteIterator($mpr);

		// Check that the input route can be returned
		self::assertSame($mpr, $iterator->getEntireRoute());

		// Check each state of the iterator until it rewinds
		$expectedStates = [
			[$route1, $route1->getBuySectorId(), TransactionType::Buy], // initial state
			[$route1, $route1->getSellSectorId(), TransactionType::Sell],
			[$route2, $route2->getBuySectorId(), TransactionType::Buy],
			[$route2, $route2->getSellSectorId(), TransactionType::Sell],
			[$route1, $route1->getBuySectorId(), TransactionType::Buy], // return to initial
		];

		foreach ($expectedStates as [$route, $sectorID, $transaction]) {
			// Check the state of the iterator
			self::assertSame($route, $iterator->getCurrentRoute());
			self::assertSame($sectorID, $iterator->getCurrentSectorID());
			self::assertSame($transaction, $iterator->getCurrentTransaction());

			// Advance the iterator
			$iterator->next();
		}
	}

}
