<?php declare(strict_types=1);

namespace Smr\Routes;

abstract class Route {

	public function getOverallExpMultiplier(): float {
		return $this->getExpMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getOverallMoneyMultiplier(): float {
		return $this->getMoneyMultiplierSum() / $this->getTurnsForRoute();
	}

	abstract public function getTurnsForRoute(): int;

	abstract public function getMoneyMultiplierSum(): int;

	abstract public function getExpMultiplierSum(): int;

	/**
	 * Provides a list of sector IDs for all ports in the Route.
	 *
	 * @return array<int>
	 */
	abstract public function getPortSectorIDs(): array;

	abstract public function containsPort(int $sectorID): bool;

	/**
	 * Recurse through the Route tree to get an ordered list.
	 *
	 * @return array<OneWayRoute>
	 */
	abstract public function getOneWayRoutes(): array;

	abstract public function getRouteString(): string;

}
