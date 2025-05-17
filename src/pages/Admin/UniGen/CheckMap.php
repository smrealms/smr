<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Exceptions\PathNotFound;
use Smr\Game;
use Smr\Location;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Plotter;
use Smr\Race;
use Smr\Routes\RouteGenerator;
use Smr\Sector;
use Smr\Template;
use Smr\TradeGood;

class CheckMap extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/check_map.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID, // for back button only
	) {}

	public function build(Account $account, Template $template): void {
		$game = Game::getGame($this->gameID);
		$template->assign('PageTopic', 'Check Map : ' . $game->getDisplayName());

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('BackHREF', $container->href());

		$galaxies = $game->getGalaxies();

		// Efficiently construct the sector cache
		foreach ($galaxies as $galaxy) {
			$galaxy->getSectors();
		}

		// Check if any locations are missing
		$existingLocs = [];
		foreach ($galaxies as $galaxy) {
			foreach ($galaxy->getLocations() as $sectorLocs) {
				foreach (array_keys($sectorLocs) as $locID) {
					$existingLocs[$locID] = true;
				}
			}
		}
		$missingLocs = array_diff(
			array_keys(Location::getAllLocations($this->gameID)),
			array_keys($existingLocs),
		);
		$missingLocNames = [];
		foreach ($missingLocs as $locID) {
			$missingLocNames[] = Location::getLocation($this->gameID, $locID)->getName();
		}
		$template->assign('MissingLocNames', $missingLocNames);

		// Find unreachable sectors
		$unreachableSectors = [];
		$lastSectorId = $game->getLastSectorID();
		$lastAccessibleSector = Sector::getSector($this->gameID, sectorID: 1);
		for ($sectorId = 2; $sectorId <= $lastSectorId; $sectorId++) {
			$checkSector = Sector::getSector($this->gameID, $sectorId);
			// Path from neighbor (or nearest accessible neighbor) is likely the shortest,
			// on average, and thus fastest way to test accessibility.
			try {
				Plotter::findDistanceToX($checkSector, $lastAccessibleSector, useFirst: true);
				$lastAccessibleSector = $checkSector;
			} catch (PathNotFound) {
				$unreachableSectors[] = $checkSector;
			}
		}
		$template->assign('UnreachableSectors', $unreachableSectors);

		// Calculate the best trade routes for each galaxy
		$tradeGoods = [GOODS_NOTHING => true];
		foreach (TradeGood::getAllIDs() as $goodID) {
			$tradeGoods[$goodID] = true;
		}
		$tradeRaces = [];
		foreach (Race::getAllIDs() as $raceID) {
			$tradeRaces[$raceID] = true;
		}

		$maxNumberOfPorts = 2;
		$routesForPort = -1;
		$numberOfRoutes = 1;
		$maxDistance = 999;

		$allGalaxyRoutes = [];
		foreach ($galaxies as $galaxy) {
			$ports = $galaxy->getPorts();
			$distances = Plotter::calculatePortToPortDistances($ports, $tradeRaces, $maxDistance, $galaxy->getStartSector(), $galaxy->getEndSector());
			$allGalaxyRoutes[$galaxy->getDisplayName()] = RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $ports, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);
		}
		$template->assign('AllGalaxyRoutes', $allGalaxyRoutes);

		$routeTypes = [
			RouteGenerator::EXP_ROUTE => 'Experience',
			RouteGenerator::MONEY_ROUTE => 'Profit',
		];
		$template->assign('RouteTypes', $routeTypes);

		// Largest port sell multipliers per galaxy
		$maxSellMultipliers = [];
		foreach ($galaxies as $galaxy) {
			$max = null;
			foreach ($galaxy->getPorts() as $port) {
				foreach ($port->getSellGoodIDs() as $goodID) {
					$distance = $port->getGoodDistance($goodID);
					// For distance ties, prefer higher good IDs
					if ($max === null || $distance > $max['Distance'] || ($distance === $max['Distance'] && $goodID > $max['GoodID'])) {
						$max = [
							'Port' => $port,
							'GoodID' => $goodID,
							'Distance' => $distance,
						];
					}
				}
			}
			if ($max !== null) {
				$output = $max['Distance'] . 'x ' . TradeGood::get($max['GoodID'])->name . ' at Port #' . $max['Port']->getSectorID() . ' (' . $max['Port']->getRaceName() . ')';
				$maxSellMultipliers[$galaxy->getDisplayName()] = $output;
			}
		}
		$template->assign('MaxSellMultipliers', $maxSellMultipliers);

	}

}
