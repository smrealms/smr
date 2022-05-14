<?php declare(strict_types=1);

namespace Smr\Routes;

abstract class Route {

	public function getOverallExpMultiplier(): float {
		return $this->getExpMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getOverallMoneyMultiplier(): float {
		return $this->getMoneyMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getTurnsForRoute(): int {
		return $this->getForwardRoute()->getTurnsForRoute() + $this->getReturnRoute()->getTurnsForRoute();
	}

	public function getMoneyMultiplierSum(): int {
		return $this->getForwardRoute()->getMoneyMultiplierSum() + $this->getReturnRoute()->getMoneyMultiplierSum();
	}

	public function getExpMultiplierSum(): int {
		return $this->getForwardRoute()->getExpMultiplierSum() + $this->getReturnRoute()->getExpMultiplierSum();
	}

	public function containsPort(int $sectorID): bool {
		$route = $this->getReturnRoute();
		return ($route != null && $route->containsPort($sectorID)) || (($route = $this->getForwardRoute()) != null && $route->containsPort($sectorID));
	}

	abstract public function getForwardRoute(): ?Route;

	abstract public function getReturnRoute(): ?OneWayRoute;

	public function getRouteString(): string {
		return $this->getForwardRoute()->getRouteString() . "\n" . $this->getReturnRoute()->getRouteString();
	}

}
