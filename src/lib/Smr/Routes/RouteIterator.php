<?php declare(strict_types=1);

namespace Smr\Routes;

use ArrayIterator;
use InfiniteIterator;

/**
 * Cyclically iterate over actions on a trade route
 */
class RouteIterator {

	private InfiniteIterator $routeIterator;
	private string $transaction = TRADER_BUYS;

	public function __construct(
		private MultiplePortRoute $route
	) {
		$oneWayRoutes = $route->getOneWayRoutes();
		$this->routeIterator = new InfiniteIterator(new ArrayIterator($oneWayRoutes));

		// PHP bug prevents IteratorIterator cache from initializing properly.
		// Just rewind to force it to populate its cache.
		$this->routeIterator->rewind();
	}

	public function getEntireRoute(): MultiplePortRoute {
		return $this->route;
	}

	public function getCurrentRoute(): OneWayRoute {
		return $this->routeIterator->current();
	}

	public function getCurrentTransaction(): string {
		return $this->transaction;
	}

	public function getCurrentSectorID(): int {
		return match ($this->transaction) {
			TRADER_BUYS => $this->getCurrentRoute()->getBuySectorId(),
			TRADER_SELLS => $this->getCurrentRoute()->getSellSectorId(),
		};
	}

	/**
	 * Advance to the next action on the route
	 */
	public function next(): void {
		if ($this->transaction == TRADER_SELLS) {
			$this->routeIterator->next();
		}
		$this->transaction = match ($this->transaction) {
			TRADER_SELLS => TRADER_BUYS,
			TRADER_BUYS => TRADER_SELLS,
		};
	}

}
