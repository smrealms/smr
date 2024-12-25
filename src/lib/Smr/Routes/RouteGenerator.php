<?php declare(strict_types=1);

namespace Smr\Routes;

use Smr\TransactionType;

class RouteGenerator {

	public const int EXP_ROUTE = 0;
	public const int MONEY_ROUTE = 1;
	/** @var array<numeric-string, array<MultiplePortRoute>> */
	private static array $expRoutes;
	/** @var array<numeric-string, array<MultiplePortRoute>> */
	private static array $moneyRoutes;
	/** @var array<int, numeric-string|int> */
	private static array $dontAddWorseThan;

	private static function initialize(): void {
		self::$expRoutes = [];
		self::$moneyRoutes = [];
		self::$dontAddWorseThan = ['0', '0'];
	}

	/**
	 * @param int $maxNumPorts
	 * @param array<int, \Smr\Port> $ports
	 * @param array<int, bool> $goods
	 * @param array<int, bool> $races
	 * @param array<int, array<int, \Smr\Path>> $distances
	 * @return array<int, array<numeric-string, array<MultiplePortRoute>>>
	 */
	public static function generateMultiPortRoutes(int $maxNumPorts, array $ports, array $goods, array $races, array $distances, int $routesForPort, int $numberOfRoutes): array {
		self::initialize();
		$routeLists = self::findOneWayRoutes($ports, $distances, $routesForPort, $goods, $races);
		$totalTasks = 0;
		foreach ($routeLists as $startSectorId => $forwardRoutes) {
			self::startRoutesToContinue($maxNumPorts, $startSectorId, $forwardRoutes, $routeLists);
			if ($totalTasks % 10 === 0 && $totalTasks > $numberOfRoutes) {
				self::trimRoutes($numberOfRoutes);
			}
			$totalTasks++;
		}
		self::trimRoutes($numberOfRoutes);
		return [
			self::EXP_ROUTE => self::$expRoutes,
			self::MONEY_ROUTE => self::$moneyRoutes,
		];
	}

	/**
	 * @param int $maxNumPorts
	 * @param int $startSectorId
	 * @param array<OneWayRoute> $forwardRoutes
	 * @param array<int, array<OneWayRoute>> $routeLists
	 */
	private static function startRoutesToContinue(int $maxNumPorts, int $startSectorId, array $forwardRoutes, array $routeLists): void {
		foreach ($forwardRoutes as $currentStepRoute) {
			$currentSellSectorId = $currentStepRoute->getSellSectorId();
			$currentGoodIsNothing = $currentStepRoute->getGoodID() === GOODS_NOTHING;
			if ($currentSellSectorId > $startSectorId) { // Not already checked
				self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $currentStepRoute, $routeLists[$currentSellSectorId], $routeLists, $currentGoodIsNothing);
			}
		}
	}

	/**
	 * @param int $maxNumPorts
	 * @param int $startSectorId
	 * @param \Smr\Routes\Route $routeToContinue
	 * @param array<OneWayRoute> $forwardRoutes
	 * @param array<int, array<OneWayRoute>> $routeLists
	 */
	private static function getContinueRoutes(int $maxNumPorts, int $startSectorId, Route $routeToContinue, array $forwardRoutes, array $routeLists, bool $lastGoodIsNothing): void {
		foreach ($forwardRoutes as $currentStepRoute) {
			$currentSellSectorId = $currentStepRoute->getSellSectorId();
			$currentGoodIsNothing = $currentStepRoute->getGoodID() === GOODS_NOTHING;
			if ($maxNumPorts === 0 && !$lastGoodIsNothing && !$currentGoodIsNothing) {
				continue; // We can only add empty one-way routes at this point
			}
			if ($lastGoodIsNothing && $currentGoodIsNothing) {
				continue; // Don't do two nothings in a row
			}
			if ($currentSellSectorId >= $startSectorId) { // Not already checked or back to start
				if ($currentSellSectorId === $startSectorId) { // Route returns to start
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::addExpRoute($mpr);
					self::addMoneyRoute($mpr);
				} elseif ($maxNumPorts > 1 && !$routeToContinue->containsPort($currentSellSectorId)) {
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $mpr, $routeLists[$currentSellSectorId], $routeLists, $currentGoodIsNothing);
				}
			}
		}
	}

	/**
	 * @param array<int, \Smr\Port> $ports
	 * @param array<int, array<int, \Smr\Path>> $distances
	 * @param int $routesForPort
	 * @param array<int, bool> $goods
	 * @param array<int, bool> $races
	 * @return array<int, array<OneWayRoute>>
	 */
	private static function findOneWayRoutes(array $ports, array $distances, int $routesForPort, array $goods, array $races): array {
		$routes = [];
		foreach ($distances as $currentSectorId => $d) {
			$currentPort = $ports[$currentSectorId];
			$raceID = $currentPort->getRaceID();
			if ($races[$raceID] === false) {
				continue;
			}
			$rl = [];
			foreach ($d as $targetSectorId => $distance) {
				$targetPort = $ports[$targetSectorId];
				if (!$races[$targetPort->getRaceID()]) {
					continue;
				}
				if ($routesForPort !== -1 && $currentSectorId !== $routesForPort && $targetSectorId !== $routesForPort) {
					continue;
				}

				foreach ($goods as $goodId => $useGood) {
					if ($useGood === true) {
						if ($goodId === GOODS_NOTHING) {
							$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $raceID, $targetPort->getRaceID(), 0, 0, $distance, GOODS_NOTHING);
						} elseif ($currentPort->hasGood($goodId, TransactionType::Buy) && $targetPort->hasGood($goodId, TransactionType::Sell)) {
							$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $raceID, $targetPort->getRaceID(), $currentPort->getGoodDistance($goodId), $targetPort->getGoodDistance($goodId), $distance, $goodId);
						}
					}
				}
			}
			$routes[$currentSectorId] = $rl;
		}
		return $routes;
	}

	private static function addExpRoute(MultiplePortRoute $r): void {
		$overallMultiplier = (string)$r->getOverallExpMultiplier(); // array keys must be string or int
		if ($overallMultiplier > self::$dontAddWorseThan[self::EXP_ROUTE]) {
			self::$expRoutes[$overallMultiplier][] = $r;
		}
	}

	private static function addMoneyRoute(MultiplePortRoute $r): void {
		$overallMultiplier = (string)$r->getOverallMoneyMultiplier(); // array keys must be string or int
		if ($overallMultiplier > self::$dontAddWorseThan[self::MONEY_ROUTE]) {
			self::$moneyRoutes[$overallMultiplier][] = $r;
		}
	}

	private static function trimRoutes(int $trimToBestXRoutes): void {
		$i = 0;
		krsort(self::$expRoutes, SORT_NUMERIC);
		foreach (self::$expRoutes as $multi => $routesByMulti) {
			if (count($routesByMulti) + $i < $trimToBestXRoutes) {
				$i += count($routesByMulti);
			} elseif ($i > $trimToBestXRoutes) {
				unset(self::$expRoutes[$multi]);
			} else {
				foreach ($routesByMulti as $key => $value) {
					$i++;
					if ($i < $trimToBestXRoutes) {
						continue;
					}
					if ($i === $trimToBestXRoutes) {
						self::$dontAddWorseThan[self::EXP_ROUTE] = $multi;
						continue;
					}
					unset(self::$expRoutes[$multi][$key]);
				}
			}
		}

		$i = 0;
		krsort(self::$moneyRoutes, SORT_NUMERIC);
		foreach (self::$moneyRoutes as $multi => $routesByMulti) {
			if (count($routesByMulti) + $i < $trimToBestXRoutes) {
				$i += count($routesByMulti);
			} elseif ($i > $trimToBestXRoutes) {
				unset(self::$moneyRoutes[$multi]);
				continue;
			} else {
				foreach ($routesByMulti as $key => $value) {
					$i++;
					if ($i < $trimToBestXRoutes) {
						continue;
					}
					if ($i === $trimToBestXRoutes) {
						self::$dontAddWorseThan[self::MONEY_ROUTE] = $multi;
						continue;
					}
					unset(self::$moneyRoutes[$multi][$key]);
				}
			}
		}
	}

}
