<?php declare(strict_types=1);

namespace Smr\Routes;

class MultiplePortRoute extends Route {

	public function __construct(
		private readonly Route $forwardRoute,
		private readonly OneWayRoute $returnRoute,
	) {}

	public function getForwardRoute(): Route {
		return $this->forwardRoute;
	}

	public function getReturnRoute(): OneWayRoute {
		return $this->returnRoute;
	}

	public function getOneWayRoutes(): array {
		return array_merge($this->forwardRoute->getOneWayRoutes(), $this->returnRoute->getOneWayRoutes());
	}

	public function containsPort(int $sectorID): bool {
		return ($this->forwardRoute->containsPort($sectorID) || $this->returnRoute->containsPort($sectorID));
	}

	public function getPortSectorIDs(): array {
		return array_unique(array_merge($this->forwardRoute->getPortSectorIDs(), $this->returnRoute->getPortSectorIDs()));
	}

	public function getTurnsForRoute(): int {
		return $this->forwardRoute->getTurnsForRoute() + $this->returnRoute->getTurnsForRoute();
	}

	public function getMoneyMultiplierSum(): int {
		return $this->forwardRoute->getMoneyMultiplierSum() + $this->returnRoute->getMoneyMultiplierSum();
	}

	public function getExpMultiplierSum(): int {
		return $this->forwardRoute->getExpMultiplierSum() + $this->returnRoute->getExpMultiplierSum();
	}

	public function getRouteString(): string {
		return $this->forwardRoute->getRouteString() . "\n" . $this->returnRoute->getRouteString();
	}

}
