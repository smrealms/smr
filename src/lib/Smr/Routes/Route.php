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

	abstract public function containsPort(int $sectorID): bool;

	abstract public function getForwardRoute(): ?Route;

	abstract public function getReturnRoute(): ?OneWayRoute;

	abstract public function getRouteString(): string;

}
