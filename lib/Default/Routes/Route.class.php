<?php declare(strict_types=1);

namespace Routes;

abstract class Route {
	public function getOverallExpMultiplier() : float {
		return $this->getExpMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getOverallMoneyMultiplier() : float {
		return $this->getMoneyMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getTurnsForRoute() : int {
		return $this->getForwardRoute()->getTurnsForRoute() + $this->getReturnRoute()->getTurnsForRoute();
	}

	public function getMoneyMultiplierSum() : int {
		return $this->getForwardRoute()->getExpMultiplierSum() + $this->getReturnRoute()->getExpMultiplierSum();
	}

	public function getExpMultiplierSum() : int {
		return $this->getForwardRoute()->getExpMultiplierSum() + $this->getReturnRoute()->getExpMultiplierSum();
	}

	public function containsPort(int $sectorID) : bool {
		$route = $this->getReturnRoute();
		return ($route != null && $route->containsPort($sectorID)) || (($route = $this->getForwardRoute()) != null && $route->containsPort($sectorID));
	}

	public abstract function getForwardRoute() : ?OneWayRoute;

	public abstract function getReturnRoute() : ?OneWayRoute;

	public function getRouteString() : string {
		return $this->getForwardRoute()->getRouteString() . "\r\n" . $this->getReturnRoute()->getRouteString();
	}
}
