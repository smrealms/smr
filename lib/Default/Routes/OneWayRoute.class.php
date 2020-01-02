<?php declare(strict_types=1);

namespace Routes;

class OneWayRoute extends Route {
	private int $sellSectorId;
	private int $buySectorId;
	private int $sellDi;
	private int $buyDi;
	private \Distance $distance;
	private int $goodId;
	private int $sellPortRace;
	private int $buyPortRace;

	/**
	 * Construct a one-way route for buying a specific trade good at one port
	 * and selling it at another.
	 * NOTE: Transactions are from the perspective of the player (not the port).
	 */
	public function __construct(int $_sellSectorId, int $_buySectorId, int $_sellPortRace, int $_buyPortRace, int $_sellDi, int $_buyDi, \Distance $_distance, int $_goodId) {
		$this->sellSectorId = $_sellSectorId;
		$this->buySectorId = $_buySectorId;
		$this->sellDi = $_sellDi;
		$this->buyDi = $_buyDi;
		$this->distance = $_distance;
		$this->goodId = $_goodId;
		$this->sellPortRace = $_sellPortRace;
		$this->buyPortRace = $_buyPortRace;
	}

	public function getSellSectorId() : int {
		return $this->sellSectorId;
	}

	public function getBuySectorId() : int {
		return $this->buySectorId;
	}

	public function getSellPortRace() : int {
		return $this->sellPortRace;
	}

	public function getBuyPortRace() : int {
		return $this->buyPortRace;
	}

	public function getSellDi() : int {
		return $this->sellDi;
	}

	public function getBuyDi() : int {
		return $this->buyDi;
	}

	public function getDistance() : \Distance {
		return $this->distance;
	}

	public function getGoodID() : int {
		return $this->goodId;
	}

	public function getOverallExpMultiplier() : float {
		return ($this->buyDi + $this->sellDi) / $this->getTurnsForRoute();
	}

	public function getMoneyMultiplierSum() : int {
		// Relations factors assume maximum relations
		$sellRelFactor = 3; // Note that this assumes maximum relations
		$buyRelFactor = 1;
		// Supply factors assume maximum supply
		$sellSupplyFactor = 2;
		$buySupplyFactor = 1;
		$goodInfo = \Globals::getGood($this->goodId);
		$buyPrice = IRound(0.03 * $goodInfo['BasePrice'] * pow($this->buyDi, 1.3) * $buyRelFactor * $buySupplyFactor);
		$sellPrice = IRound(0.08 * $goodInfo['BasePrice'] * pow($this->sellDi, 1.3) * $sellRelFactor * $sellSupplyFactor);
		return $sellPrice - $buyPrice;
	}

	public function getExpMultiplierSum() : int {
		return $this->buyDi + $this->sellDi;
	}

	public function getTurnsForRoute() : int {
		if ($this->goodId === GOOD_NOTHING)
			$tradeTurns = 0;
		else
			$tradeTurns = 2 * TURNS_PER_TRADE;
		return $this->distance->getTurns() + $tradeTurns;
	}

	public function containsPort(int $sectorID) : bool {
		return $this->sellSectorId == $sectorID || $this->buySectorId == $sectorID;
	}

	public function getForwardRoute() : ?OneWayRoute {
		return null;
	}

	public function getReturnRoute() : ?OneWayRoute {
		return null;
	}

	public function getRouteString() : string {
		return $this->buySectorId . ' (' . \Globals::getRaceName($this->buyPortRace) . ') buy ' . \Globals::getGoodName($this->goodId) . ' at ' . $this->buyDi . 'x to sell at (Distance: ' . $this->distance->getDistance() . ($this->distance->getNumWarps() > 0 ? ' + ' . $this->distance->getNumWarps() . ' warps) ' : ') ') . $this->sellSectorId . ' (' . \Globals::getRaceName($this->sellPortRace) . ') at ' . $this->sellDi . 'x';
	}
}
