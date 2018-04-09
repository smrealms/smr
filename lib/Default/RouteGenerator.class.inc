<?php

require_once('Plotter.class.inc');
require_once(get_file_loc('SmrSector.class.inc'));

const ROUTE_GEN_USE_RELATIONS_FACTOR = false;

class RouteGenerator {
	const GOOD_BUYS = 'Buy';
	const GOOD_SELLS = 'Sell';

	const EXP_ROUTE = 0;
	const MONEY_ROUTE = 1;
	static $expRoutes;
	static $moneyRoutes;
	static $dontAddWorseThan = array(0, 0);

	public static function &generateMultiPortRoutes($maxNumPorts, array &$sectors, array &$goods, array &$races, array &$distances, $routesForPort, $numberOfRoutes) {
		return self::findMultiPortRoutes($maxNumPorts, self::findOneWayRoutes($sectors, $distances, $routesForPort, $goods, $races), $numberOfRoutes);
	}

	private static function &findMultiPortRoutes($maxNumPorts, array &$routeLists, $numberOfRoutes) {
		self::$expRoutes = array();
		self::$moneyRoutes = array();
		$runs = array();
		$totalTasks = 0;
		foreach($routeLists as $key => &$value) {
			self::startRoutesToContinue($maxNumPorts, $key, $value, $routeLists);
			if ($totalTasks % 10 === 0 && $totalTasks > $numberOfRoutes) {
				self::trimRoutes($numberOfRoutes);
			}
			$totalTasks++;
		} unset($value);
//		$tasksCompleted = 0;
		self::trimRoutes($numberOfRoutes);
		$allRoutes = array();
		$allRoutes[self::EXP_ROUTE] =& self::$expRoutes;
		$allRoutes[self::MONEY_ROUTE] =& self::$moneyRoutes;
		return $allRoutes;
	}

	/**
	 * Works by pass by reference so will update higher levels, hacky but works.
	 *
	 * @param startSectorId
	 * @param forwardRoutes
	 * @param routeLists
	 * @param expRoutes
	 */
	static function startRoutesToContinue($maxNumPorts, $startSectorId, array &$forwardRoutes, array &$routeLists) {
		foreach($forwardRoutes as &$currentStepRoute) {
			$currentStepBuySector = $currentStepRoute->getBuySectorId();
			if ($currentStepBuySector > $startSectorId) { // Not already checked
				self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $currentStepRoute, $routeLists[$currentStepBuySector], $routeLists, $currentStepRoute->getGoodID() === GOOD_NOTHING);
			}
		} unset($currentStepRoute);
	}

	/**
	 * Works by pass by reference so will update higher levels, hacky but works.
	 *
	 * @param startSectorId
	 * @param routeToContinue
	 * @param forwardRoutes
	 * @param routeLists
	 * @param allRoutes
	 */
	private static function getContinueRoutes($maxNumPorts, $startSectorId, Route &$routeToContinue, array &$forwardRoutes, array &$routeLists, $lastGoodIsNothing) {
		foreach($forwardRoutes as &$currentStepRoute) {
			$currentStepBuySector = $currentStepRoute->getBuySectorId();
			if ($lastGoodIsNothing & ($lastGoodIsNothing = GOOD_NOTHING === $currentStepRoute->getGoodID()))
				continue; // Don't do two nothings in a row
			if ($currentStepBuySector >= $startSectorId) { // Not already checked or back to start
				if ($currentStepBuySector === $startSectorId) { // Route returns to start
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::addExpRoute($mpr);
					self::addMoneyRoute($mpr);
				}
				else if ($maxNumPorts > 1 && !$routeToContinue->containsPort($currentStepBuySector)) {
					$mpr = new MultiplePortRoute($routeToContinue, $currentStepRoute);
					self::getContinueRoutes($maxNumPorts - 1, $startSectorId, $mpr, $routeLists[$currentStepBuySector], $routeLists, $lastGoodIsNothing);
				}
			}
		} unset($currentStepRoute);
	}

	private static function &findOneWayRoutes(array &$sectors, array &$distances, $routesForPort, array &$goods, array &$races) {
		$routes = array();
		foreach($distances as $currentSectorId => &$d) {
			$raceID = $sectors[$currentSectorId]->getPort()->getRaceID();
			if (isset($races[$raceID])===false) {
				echo 'Error with Race ID: '.$sectors[$currentSectorId]->getPort()->getRaceID();
				continue;
			}
			if($races[$raceID]===false)
				continue;
			$rl = array();
			foreach($d as $targetSectorId => &$distance) {
				if (!$races[$sectors[$targetSectorId]->getPort()->getRaceID()])
					continue;
				if($routesForPort!==-1 && $currentSectorId !== $routesForPort && $targetSectorId !== $routesForPort)
					continue;

				if ($goods[GOOD_NOTHING]===true)
					$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $races[$raceID], $sectors[$targetSectorId]->getPort()->getRaceID(), 0, 0, $distance, GOOD_NOTHING);

				$gameGoods = Globals::getGoods();
				foreach($gameGoods as $goodId => &$value) {
					if ($goods[$goodId]===true) {
						if ($sectors[$currentSectorId]->getPort()->getGoodTransaction($goodId) === self::GOOD_SELLS &&
						    $sectors[$targetSectorId]->getPort()->getGoodTransaction($goodId) === self::GOOD_BUYS) {
							$rl[] = new OneWayRoute($currentSectorId, $targetSectorId, $races[$raceID], $sectors[$targetSectorId]->getPort()->getRaceID(), $sectors[$currentSectorId]->getPort()->getGoodDistance($goodId), $sectors[$targetSectorId]->getPort()->getGoodDistance($goodId), $distance, $goodId);
						}
					}
				} unset($value);
			} unset($distance);
			$routes[$sectors[$currentSectorId]->getSectorID()] = $rl;
		} unset($d);
		return $routes;
	}

	public static function &generateOneWayRoutes(array &$sectors, array &$distances, array &$goods, &$races, &$routesForPort) {
		self::$expRoutes = array();
		self::$moneyRoutes = array();
		foreach($distances as $currentSectorId => &$d) {
			if ($races[$sectors[$currentSectorId]->getPort()->getRaceID()]===false)
				continue;
			foreach($d as $targetSectorId => &$distance) {
				if ($races[$sectors[$targetSectorId]->getPort()->getRaceID()]===false)
					continue;
				if($routesForPort!==-1 && $currentSectorId !== $routesForPort && $targetSectorId !== $routesForPort)
					continue;

				$gameGoods =& Globals::getGoods();
				foreach($gameGoods as $goodId => &$value) {
					if ($goods[$goodId]===true) {
						if ($sectors[$currentSectorId]->getPort()->getGoodTransaction($goodId) === self::GOOD_SELLS &&
						    $sectors[$targetSectorId]->getPort()->getGoodTransaction($goodId) === self::GOOD_BUYS) {
							$owr = new OneWayRoute($currentSectorId, $targetSectorId, $sectors[$currentSectorId]->getPort()->getRaceID(), $sectors[$targetSectorId]->getPort()->getRaceID(), $sectors[$currentSectorId]->getPort()->getGoodDistance($goodId), $sectors[$targetSectorId]->getPort()->getGoodDistance($goodId), $distance, $goodId);
							$fakeReturn = new OneWayRoute($targetSectorId, $currentSectorId, $sectors[$targetSectorId]->getPort()->getRaceID(), $sectors[$currentSectorId]->getPort()->getRaceID(), 0, 0, $distance, GOOD_NOTHING);
							$mpr = new MultiplePortRoute($owr, $fakeReturn);
							self::addExpRoute($mpr);
							self::addMoneyRoute($mpr);
						}
					}
				} unset($value);
			} unset($distance);
		} unset($d);
		$allRoutes = array();
		$allRoutes[self::EXP_ROUTE] = self::$expRoutes;
		$allRoutes[self::MONEY_ROUTE] = self::$moneyRoutes;
		return $allRoutes;
	}

	private static function addExpRoute(Route &$r) {
		$overallMultiplier = $r->getOverallExpMultiplier();
		if ($overallMultiplier > self::$dontAddWorseThan[self::EXP_ROUTE]) {
			if (isset(self::$expRoutes[$overallMultiplier])) {
				$rl =& self::$expRoutes[$overallMultiplier];
			}
			else {
				$rl = array();
			}
			$rl[] =& $r;
			self::$expRoutes[$overallMultiplier] =& $rl;
		}
	}

	private static function addMoneyRoute(Route &$r) {
		$overallMultiplier = $r->getOverallMoneyMultiplier();
		if ($overallMultiplier > self::$dontAddWorseThan[self::MONEY_ROUTE]) {
			if (isset(self::$moneyRoutes[$overallMultiplier])) {
				$rl =& self::$moneyRoutes[$overallMultiplier];
			}
			else {
				$rl = array();
			}
			$rl[] =& $r;
			self::$moneyRoutes[$overallMultiplier] =& $rl;
		}
	}

	public static function trimRoutes($trimToBestXRoutes) {
		$i = 0;
		krsort(self::$expRoutes);
		foreach(self::$expRoutes as $multi => &$routesByMulti) {
			if(count($routesByMulti)+$i < $trimToBestXRoutes) {
				$i += count($routesByMulti);
			}
			else if($i > $trimToBestXRoutes) {
				unset(self::$expRoutes[$multi]);
			}
			else {
				foreach($routesByMulti as $key => &$value) {
					$i++;
					if($i < $trimToBestXRoutes)
						continue;
					if($i === $trimToBestXRoutes) {
						self::$dontAddWorseThan[self::EXP_ROUTE] = $multi;
						continue;
					}
					unset(self::$expRoutes[$multi][$key]);
				} unset($value);
			}
		} unset($routesByMulti);

		$i = 0;
		krsort(self::$moneyRoutes);
		foreach(self::$moneyRoutes as $multi => &$routesByMulti) {
			if(count($routesByMulti)+$i < $trimToBestXRoutes) {
				$i += count($routesByMulti);
			}
			else if($i > $trimToBestXRoutes) {
				unset(self::$moneyRoutes[$multi]);
				continue;
			}
			else {
				foreach($routesByMulti as $key => &$value) {
					$i++;
					if($i < $trimToBestXRoutes)
						continue;
					if($i === $trimToBestXRoutes) {
						self::$dontAddWorseThan[self::MONEY_ROUTE] = $multi;
						continue;
					}
					unset(self::$moneyRoutes[$multi][$key]);
				} unset($value);
			}
		} unset($routesByMulti);
	}
}



abstract class Route {
	public function getOverallExpMultiplier() {
		return $this->getExpMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getOverallMoneyMultiplier() {
		return $this->getMoneyMultiplierSum() / $this->getTurnsForRoute();
	}

	public function getTurnsForRoute() {
		return $this->getForwardRoute()->getTurnsForRoute() + $this->getReturnRoute()->getTurnsForRoute();
	}

	public function getMoneyMultiplierSum() {
		return $this->getForwardRoute()->getExpMultiplierSum() + $this->getReturnRoute()->getExpMultiplierSum();
	}

	public function getExpMultiplierSum() {
		return $this->getForwardRoute()->getExpMultiplierSum() + $this->getReturnRoute()->getExpMultiplierSum();
	}

	public function containsPort($sectorID) {
		$route = $this->getReturnRoute();
		return ($route != null && $route->containsPort($sectorID)) || (($route = $this->getForwardRoute()) != null && $route->containsPort($sectorID));
	}

	public abstract function &getForwardRoute();

	public abstract function &getReturnRoute();

	public function getRouteString() {
		return $this->getForwardRoute()->getRouteString() . "\r\n" . $this->getReturnRoute()->getRouteString();
	}
}

class OneWayRoute extends Route {
	private $sellSectorId;
	private $buySectorId;
	private $sellDi;
	private $buyDi;
	private $distance;
	private $goodId;
	private $sellPortRace;
	private $buyPortRace;

	public function __construct($_sellSectorId, $_buySectorId, $_sellPortRace, $_buyPortRace, $_sellDi, $_buyDi, Distance &$_distance, $_goodId) {
		$this->sellSectorId = $_sellSectorId;
		$this->buySectorId = $_buySectorId;
		$this->sellDi = $_sellDi;
		$this->buyDi = $_buyDi;
		$this->distance =& $_distance;
		$this->goodId = $_goodId;
		$this->sellPortRace = $_sellPortRace;
		$this->buyPortRace = $_buyPortRace;
	}

	public function setSellSectorId($value) {
		$this->sellSectorId = $value;
	}

	public function setBuySectorId($value) {
		$this->buySectorId = $value;
	}

	public function setSellPortRace($value) {
		$this->sellPortRace = $value;
	}

	public function setBuyPortRace($value) {
		$this->buyPortRace = $value;
	}

	public function setSellDi($value) {
		$this->sellDi = $value;
	}

	public function setBuyDi($value) {
		$this->buyDi = $value;
	}

	public function setDistance(Distance &$value) {
		$this->distance =& $value;
	}

	public function setGoodId($value) {
		$this->goodId = $value;
	}

	public function getSellSectorId() {
		return $this->sellSectorId;
	}

	public function getBuySectorId() {
		return $this->buySectorId;
	}

	public function getSellPortRace() {
		return $this->sellPortRace;
	}

	public function getBuyPortRace() {
		return $this->buyPortRace;
	}

	public function getSellDi() {
		return $this->sellDi;
	}

	public function getBuyDi() {
		return $this->buyDi;
	}

	public function &getDistance() {
		return $this->distance;
	}

	public function getGoodID() {
		return $this->goodId;
	}

	public function getOverallExpMultiplier() {
		return ($this->buyDi + $this->sellDi) / getTurnsForRoute();
	}

	public function getMoneyMultiplierSum() {// TODO sellDi stuff and check accuracy of formula
		$buyRelFactor = 1;
		$sellRelFactor = 1;
		if (ROUTE_GEN_USE_RELATIONS_FACTOR===true) {
			//TODO: This needs to be converted for PHP/SMR.
//			$relations = max(PlayerPreferences.getRelationsForRace($this->buyPortRace), Settings.MAX_MONEY_RELATIONS);
//			$buyRelFactor = ($relations + 350) / 8415.0;
//
//			$sellRelFactor = 2 - (PlayerPreferences.getRelationsForRace($this->sellPortRace) + 50) / 850.0 * ((relations + 350)/1500);
		}
		$goodInfo = Globals::getGood($this->goodId);
		$buyPrice = round(($goodInfo['BasePrice'] * 0.6 * pow($this->buyDi + .5, 1.8) * $buyRelFactor));
		$sellPrice = round(($goodInfo['BasePrice'] * 0.5 * pow($this->sellDi, 1.84) * $sellRelFactor / 38.56	/*
																												* * (1 +
																												* (10 -
																												* $port_lvl) /
																												* 50)
																												*/));
		return $buyPrice - $sellPrice;
	}

	public function getExpMultiplierSum() {
		return $this->buyDi + $this->sellDi;
	}

	public function getTurnsForRoute() {
		if ($this->goodId === GOOD_NOTHING)
			$tradeTurns = 0;
		else
			$tradeTurns = 2 * TURNS_PER_TRADE;
		return $this->distance->getTurns() + $tradeTurns;
	}

	public function compareTo(OneWayRoute &$compare) {
		if ($this->equals($compare)===true)
			return 0;
		if ($this->getOverallExpMultiplier() > $compare->getOverallExpMultiplier())
			return 1;
		return -1;
	}

	public function containsPort($sectorID) {
		return $this->sellSectorId == $sectorID || $this->buySectorId == $sectorID;
	}

	public function &getForwardRoute() {
		return null;
	}

	public function &getReturnRoute() {
		return null;
	}

	public function getRouteString() {
		return $this->sellSectorId . ' (' . Globals::getRaceName($this->sellPortRace) . ') buy ' . Globals::getGoodName($this->goodId) . ' at ' . $this->sellDi . 'x to sell at (Distance: ' . $this->distance->getDistance() . ($this->distance->getNumWarps() > 0 ? ' + ' . $this->distance->getNumWarps() . ' warps) ' : ') ') . $this->buySectorId . ' (' . Globals::getRaceName($this->buyPortRace) . ') at ' . $this->buyDi . 'x';
	}
}

class MultiplePortRoute extends Route {
	private $forwardRoute;
	private $returnRoute;

	public function __construct(Route &$_forwardRoute, Route &$_returnRoute) {
		$this->forwardRoute =& $_forwardRoute;
		$this->returnRoute =& $_returnRoute;
	}

	public function setForwardRoute(Route &$r) {
		$this->forwardRoute =& $r;
	}

	public function setReturnRoute(Route &$r) {
		$this->returnRoute =& $r;
	}

	public function &getForwardRoute() {
		return $this->forwardRoute;
	}

	public function &getReturnRoute() {
		return $this->returnRoute;
	}
}
?>
