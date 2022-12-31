<?php declare(strict_types=1);

namespace Smr\Routes;

use Smr\Path;
use Smr\Race;
use Smr\TradeGood;
use Smr\TransactionType;
use SmrPort;

class OneWayRoute extends Route {

	/**
	 * Construct a one-way route for buying a specific trade good at one port
	 * and selling it at another.
	 * NOTE: Transactions are from the perspective of the player (not the port).
	 */
	public function __construct(
		private readonly int $buySectorId,
		private readonly int $sellSectorId,
		private readonly int $buyPortRace,
		private readonly int $sellPortRace,
		private readonly int $buyDi,
		private readonly int $sellDi,
		private readonly Path $path,
		private readonly int $goodId,
	) {}

	public function getSellSectorId(): int {
		return $this->sellSectorId;
	}

	public function getBuySectorId(): int {
		return $this->buySectorId;
	}

	public function getSellPortRace(): int {
		return $this->sellPortRace;
	}

	public function getBuyPortRace(): int {
		return $this->buyPortRace;
	}

	public function getSellDi(): int {
		return $this->sellDi;
	}

	public function getBuyDi(): int {
		return $this->buyDi;
	}

	public function getPath(): Path {
		return $this->path;
	}

	public function getGoodID(): int {
		return $this->goodId;
	}

	public function getMoneyMultiplierSum(): int {
		if ($this->goodId === GOODS_NOTHING) {
			return 0;
		}
		$numGoods = 1;
		$relations = 1000; // assume max relations
		$supply = TradeGood::get($this->goodId)->maxPortAmount; // assume max supply
		$buyPrice = SmrPort::idealPrice($this->goodId, TransactionType::Buy, $numGoods, $relations, $supply, $this->buyDi);
		$sellPrice = SmrPort::idealPrice($this->goodId, TransactionType::Sell, $numGoods, $relations, $supply, $this->sellDi);
		return $sellPrice - $buyPrice;
	}

	public function getExpMultiplierSum(): int {
		return $this->buyDi + $this->sellDi;
	}

	public function getTurnsForRoute(): int {
		if ($this->goodId === GOODS_NOTHING) {
			$tradeTurns = 0;
		} else {
			$tradeTurns = 2 * TURNS_PER_TRADE;
		}
		return $this->path->getTurns() + $tradeTurns;
	}

	public function getPortSectorIDs(): array {
		return [$this->buySectorId, $this->sellSectorId];
	}

	public function containsPort(int $sectorID): bool {
		return $this->sellSectorId == $sectorID || $this->buySectorId == $sectorID;
	}

	public function getOneWayRoutes(): array {
		return [$this];
	}

	public function getGoodName(): string {
		if ($this->goodId == GOODS_NOTHING) {
			return 'Nothing';
		}
		return TradeGood::get($this->goodId)->name;
	}

	public function getRouteString(): string {
		$buy = $this->buySectorId . ' (' . Race::getName($this->buyPortRace) . ') buy ' . $this->getGoodName() . ' for ' . $this->buyDi . 'x';
		$sell = ' to sell at ' . $this->sellSectorId . ' (' . Race::getName($this->sellPortRace) . ') for ' . $this->sellDi . 'x';
		$distance = ' (Distance: ' . $this->path->getDistance() . ($this->path->getNumWarps() > 0 ? ' + ' . $this->path->getNumWarps() . ' warps) ' : ')');
		return $buy . $sell . $distance;
	}

}
