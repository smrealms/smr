<?php declare(strict_types=1);

namespace Smr\Routes;

use Smr\StdlibExtensions\InfiniteArrayIterator;
use Smr\TransactionType;

/**
 * Cyclically iterate over actions on a trade route
 */
class RouteIterator {

	/** @var InfiniteArrayIterator<int, OneWayRoute> */
	private readonly InfiniteArrayIterator $routeIterator;

	private TransactionType $transaction = TransactionType::Buy;

	public function __construct(
		private readonly MultiplePortRoute $route,
	) {
		$oneWayRoutes = $route->getOneWayRoutes();
		$this->routeIterator = new InfiniteArrayIterator($oneWayRoutes);
	}

	public function getEntireRoute(): MultiplePortRoute {
		return $this->route;
	}

	public function getCurrentRoute(): OneWayRoute {
		return $this->routeIterator->current();
	}

	public function getCurrentTransaction(): TransactionType {
		return $this->transaction;
	}

	public function getCurrentSectorID(): int {
		return match ($this->transaction) {
			TransactionType::Buy => $this->getCurrentRoute()->getBuySectorId(),
			TransactionType::Sell => $this->getCurrentRoute()->getSellSectorId(),
		};
	}

	/**
	 * Advance to the next action on the route
	 */
	public function next(): void {
		if ($this->transaction === TransactionType::Sell) {
			$this->routeIterator->next();
		}
		$this->transaction = $this->transaction->opposite();
	}

}
