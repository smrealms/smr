<?php declare(strict_types=1);

namespace Routes;

class RouteGenerator {

	const EXP_ROUTE = 0;
	const MONEY_ROUTE = 1;
	static array $expRoutes;
	static array $moneyRoutes;
	static array $dontAddWorseThan;

	private static function initialize() : void {
		self::$expRoutes = [];
		self::$moneyRoutes = [];
		self::$dontAddWorseThan = [0, 0];
	}

	public static function generateMultiPortRoutes(int $maxNumPorts, array $sectors, array $goods, array $races, array $distances, int $routesForPort, int $numberOfRoutes) : array {
		self::initialize();
		$routeLists = self::findOneWayRoutes($sectors, $distances, $routesForPort, $goods, $races);
		$totalTasks = 0;
		foreach ($routeLists as $key => $value) {
			self::startRoutesToContinue($maxNumPorts, $key, $value, $routeLists);
			if ($totalTasks % 10 === 0 && $totalTasks > $numberOfRoutes) {
				self::trimRoutes($numberOfRoutes);
			}
			$totalTasks++;
		}
		self::trimRoutes($numberOfRoutes);
		$allRoutes = [
			self::EXP_ROUTE => self::$expRoutes,
			self::MONEY_ROUTE => self::$moneyRoutes,
		];
		return $allRoutes;
	}

	private static function startRoutesToContinue(int $maxNumPorts, int $startSectorId, array $forwardRoutes, array $routeLists) : void {
		foreach ($forwardRoutes as $currentStepRoute) {
			$currentStepBuySector = $currentStepRoute->getBuySectorId();
			if ($currentStepBuySector > $startSectorId) { // Not already checked
				self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $currentStepRoute, $routeLists[$currentStepBuySector], $routeLists, $currentStepRoute->getGoodID() === GOODS_NOTHING);
			}
		}
	}

	private static function getContinueRoutes(int $maxNumPorts, int $startSectorId, Route $routeToContinue, array $forwardRoutes, array $routeLists, bool $lastGoodIsNothing) : void {
		foreach ($forwardRoutes as $currentStepRoute) {
			$currentStepBuySector = $currentStepRoute->getBuySectorId();
			if ($lastGoodIsNothing && ($lastGoodIsNothing = GOODS_NOTHING === $currentStepRoute->getGoodID())) {
				continue; // Don't do two nothings in a row
			}
			if ($currentStepBuySector >= $startSectorId) { // Not already checked or back to start
				if ($currentStepBuySector === $startSectorId) { // Route returns to start
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::addExpRoute($mpr);
					self::addMoneyRoute($mpr);
				} elseif ($maxNumPorts > 1 && !$routeToContinue->containsPort($currentStepBuySector)) {
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $mpr, $routeLists[$currentStepBuySector], $routeLists, $lastGoodIsNothing);
				}
			}
		}
	}

	/**
	 * @return array<int, array<OneWayRoute>>
	 */
	private static function findOneWayRoutes(array $sectors, array $distances, int $routesForPort, array $goods, array $races) : array {
		$routes = array();
		foreach ($distances as $currentSectorId => $d) {
			$currentPort = $sectors[$currentSectorId]->getPort();
			$raceID = $currentPort->getRaceID();
			if ($races[$raceID] === false) {
				continue;
			}
			$rl = array();
			foreach ($d as $targetSectorId => $distance) {
				$targetPort = $sectors[$targetSectorId]->getPort();
				if (!$races[$targetPort->getRaceID()]) {
					continue;
				}
				if ($routesForPort !== -1 && $currentSectorId !== $routesForPort && $targetSectorId !== $routesForPort) {
					continue;
				}

				if ($goods[GOODS_NOTHING] === true) {
					$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $raceID, $targetPort->getPort()->getRaceID(), 0, 0, $distance, GOODS_NOTHING);
				}

				foreach (\Globals::getGoods() as $goodId => $value) {
					if ($goods[$goodId] === true) {
						if ($currentPort->hasGood($goodId, TRADER_SELLS) && $targetPort->hasGood($goodId, TRADER_BUYS)) {
							$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $raceID, $targetPort->getRaceID(), $currentPort->getGoodDistance($goodId), $targetPort->getGoodDistance($goodId), $distance, $goodId);
						}
					}
				}
			}
			$routes[$sectors[$currentSectorId]->getSectorID()] = $rl;
		}
		return $routes;
	}

	public static function generateOneWayRoutes(array $sectors, array $distances, array $goods, array $races, int $routesForPort) : array {
		self::initialize();
		foreach ($distances as $currentSectorId => $d) {
			$currentPort = $sectors[$currentSectorId]->getPort();
			if ($races[$currentPort->getRaceID()] === false) {
				continue;
			}
			foreach ($d as $targetSectorId => $distance) {
				$targetPort = $sectors[$targetSectorId]->getPort();
				if ($races[$targetPort->getRaceID()] === false) {
					continue;
				}
				if ($routesForPort !== -1 && $currentSectorId !== $routesForPort && $targetSectorId !== $routesForPort) {
					continue;
				}

				foreach (\Globals::getGoods() as $goodId => $value) {
					if ($goods[$goodId] === true) {
						if ($currentPort->hasGood($goodId, TRADER_SELLS) && $targetPort->hasGood($goodId, TRADER_BUYS)) {
							$owr = new OneWayRoute($currentSectorId, $targetSectorId, $currentPort->getRaceID(), $targetPort->getRaceID(), $currentPort->getGoodDistance($goodId), $targetPort->getGoodDistance($goodId), $distance, $goodId);
							$fakeReturn = new OneWayRoute($targetSectorId, $currentSectorId, $targetPort->getRaceID(), $currentPort->getRaceID(), 0, 0, $distance, GOODS_NOTHING);
							$mpr = new MultiplePortRoute($owr, $fakeReturn);
							self::addExpRoute($mpr);
							self::addMoneyRoute($mpr);
						}
					}
				}
			}
		}
		$allRoutes = [
			self::EXP_ROUTE => self::$expRoutes,
			self::MONEY_ROUTE => self::$moneyRoutes,
		];
		return $allRoutes;
	}

	private static function addExpRoute(Route $r) : void {
		$overallMultiplier = strval($r->getOverallExpMultiplier()); // array keys must be string or int
		if ($overallMultiplier > self::$dontAddWorseThan[self::EXP_ROUTE]) {
			if (isset(self::$expRoutes[$overallMultiplier])) {
				self::$expRoutes[$overallMultiplier][] = $r;
			} else {
				self::$expRoutes[$overallMultiplier] = [$r];
			}
		}
	}

	private static function addMoneyRoute(Route $r) : void {
		$overallMultiplier = strval($r->getOverallMoneyMultiplier()); // array keys must be string or int
		if ($overallMultiplier > self::$dontAddWorseThan[self::MONEY_ROUTE]) {
			if (isset(self::$moneyRoutes[$overallMultiplier])) {
				self::$moneyRoutes[$overallMultiplier][] = $r;
			} else {
				self::$moneyRoutes[$overallMultiplier] = [$r];
			}
		}
	}

	private static function trimRoutes(int $trimToBestXRoutes) : void {
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
