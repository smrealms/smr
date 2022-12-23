<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Exceptions\UserError;
use Smr\Page\AccountPageProcessor;
use Smr\PlanetTypes\PlanetType;
use Smr\Race;
use Smr\Request;
use SmrAccount;
use SmrGalaxy;
use SmrLocation;
use SmrPort;
use SmrSector;

/**
 * @param array<int, SmrSector> $sectors
 * @param callable $condition True if sector is valid
 */
function findValidSector(array $sectors, callable $condition): SmrSector {
	if (count($sectors) == 0) {
		throw new UserError('There are no eligible sectors for this action!');
	}
	$key = array_rand($sectors);
	$sector = $sectors[$key];
	if ($condition($sector) !== true) {
		unset($sectors[$key]);
		return findValidSector($sectors, $condition);
	}
	return $sector;
}

function checkSectorAllowedForLoc(SmrSector $sector, SmrLocation $location): bool {
	if ($location->isHQ()) {
		// Only add HQs to empty sectors
		return !$sector->hasLocation();
	}
	// Otherwise, sector must meet these conditions:
	// 1. Does not already have this location
	// 2. Has fewer than 4 other locations
	// 3. Does not offer Fed protection
	return count($sector->getLocations()) < 4 && !$sector->offersFederalProtection() && !$sector->hasLocation($location->getTypeID());
}

function addLocationToSector(SmrLocation $location, SmrSector $sector): void {
	$sector->addLocation($location); //insert the location
	if ($location->isHQ()) {
		//only playable races have extra locations to add
		//Racial/Fed
		foreach ($location->getLinkedLocations() as $linkedLocation) {
			if (!$sector->hasLocation($linkedLocation->getTypeID())) {
				$sector->addLocation($linkedLocation);
			}
			if ($linkedLocation->isFed()) {
				$fedBeacon = $linkedLocation;
			}
		}

		//add Beacons to all surrounding areas (up to 2 sectors out)
		$visitedSectors = [];
		$links = ['Up', 'Right', 'Down', 'Left'];
		$fedSectors = [$sector];
		$tempFedSectors = [];
		for ($i = 0; $i < DEFAULT_FED_RADIUS; $i++) {
			foreach ($fedSectors as $fedSector) {
				foreach ($links as $link) {
					if ($fedSector->hasLink($link) && !isset($visitedSectors[$fedSector->getLink($link)])) {
						$linkSector = $sector->getLinkSector($link);
						if (isset($fedBeacon) && !$linkSector->hasLocation($fedBeacon->getTypeID())) {
							$linkSector->addLocation($fedBeacon); //add beacon to this sector
						}
						$tempFedSectors[] = $linkSector;
						$visitedSectors[$fedSector->getLink($link)] = true;
					}
				}
			}
			$fedSectors = $tempFedSectors;
			$tempFedSectors = [];
		}
	}
}


class SaveProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		$submit = Request::get('submit');

		if ($submit == 'Redo Connections') {
			$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
			$connectivity = Request::getFloat('connect');
			if (!$galaxy->setConnectivity($connectivity)) {
				$message = '<span class="red">Error</span> : Regenerating connections failed.';
			} else {
				$message = '<span class="green">Success</span> : Regenerated connectivity with ' . $connectivity . '% target.';
			}
			SmrSector::saveSectors();
		} elseif ($submit == 'Create Locations') {
			$galSectors = SmrSector::getGalaxySectors($this->gameID, $this->galaxyID);
			foreach ($galSectors as $galSector) {
				$galSector->removeAllLocations();
			}
			foreach (SmrLocation::getAllLocations($this->gameID) as $location) {
				if (Request::has('loc' . $location->getTypeID())) {
					$numLoc = Request::getInt('loc' . $location->getTypeID());
					for ($i = 0; $i < $numLoc; $i++) {
						//4 per sector max locs and no locations inside fed
						$randSector = findValidSector(
							$galSectors,
							fn(SmrSector $sector): bool => checkSectorAllowedForLoc($sector, $location)
						);
						addLocationToSector($location, $randSector);
					}
				}
			}
			$message = '<span class="green">Success</span> : Succesfully added locations.';
		} elseif ($submit == 'Create Warps') {
			//get all warp info from all gals, some need to be removed, some need to be added
			$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
			$galSectors = $galaxy->getSectors();
			//get totals
			foreach ($galSectors as $galSector) {
				if ($galSector->hasWarp()) {
					$galSector->removeWarp();
				}
			}
			//iterate over all the galaxies
			$galaxies = SmrGalaxy::getGameGalaxies($this->gameID);
			foreach ($galaxies as $eachGalaxy) {
				//do we have a warp to this gal?
				if (Request::has('warp' . $eachGalaxy->getGalaxyID())) {
					// Sanity check the number
					$numWarps = Request::getInt('warp' . $eachGalaxy->getGalaxyID());
					if ($numWarps > 10) {
						create_error('Specify no more than 10 warps between two galaxies!');
					}
					//iterate for each warp to this gal
					for ($i = 1; $i <= $numWarps; $i++) {
						//only 1 warp per sector
						$galSector = findValidSector(
							$galSectors,
							fn(SmrSector $sector): bool => !$sector->hasWarp() && !$sector->offersFederalProtection()
						);
						//get other side
						//make sure it does not go to itself
						$otherSector = findValidSector(
							$eachGalaxy->getSectors(),
							fn(SmrSector $sector): bool => !$sector->hasWarp() && !$sector->offersFederalProtection() && !$sector->equals($galSector)
						);
						$galSector->setWarp($otherSector);
					}
				}
			}
			SmrSector::saveSectors();
			$message = '<span class="green">Success</span> : Succesfully added warps.';
			(new CreateWarps($this->gameID, $this->galaxyID, $message))->go();
		} elseif ($submit == 'Create Planets') {
			$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
			$galSectors = $galaxy->getSectors();
			foreach ($galSectors as $galSector) {
				if ($galSector->hasPlanet()) {
					$galSector->removePlanet();
				}
			}

			foreach (array_keys(PlanetType::PLANET_TYPES) as $planetTypeID) {
				$numberOfPlanets = Request::getInt('type' . $planetTypeID);
				for ($i = 1; $i <= $numberOfPlanets; $i++) {
					$galSector = findValidSector(
						$galSectors,
						fn(SmrSector $sector): bool => !$sector->hasPlanet() // 1 per sector
					);
					$galSector->createPlanet($planetTypeID);
				}
			}
			$message = '<span class="green">Success</span> : Succesfully added planets.';
		} elseif ($submit == 'Create Ports') {
			$numLevelPorts = [];
			$maxPortLevel = SmrPort::getMaxLevelByGame($this->gameID);
			for ($i = 1; $i <= $maxPortLevel; $i++) {
				$numLevelPorts[$i] = Request::getInt('port' . $i);
			}
			$totalPorts = array_sum($numLevelPorts);

			$totalRaceDist = 0;
			$numRacePorts = [];
			foreach (Race::getAllIDs() as $raceID) {
				$racePercent = Request::getInt('race' . $raceID);
				if (!empty($racePercent)) {
					$totalRaceDist += $racePercent;
					$numRacePorts[$raceID] = ceil($racePercent / 100 * $totalPorts);
				}
			}
			$assignedPorts = array_sum($numRacePorts);
			if ($totalRaceDist == 100 || $totalPorts == 0) {
				$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
				$galSectors = $galaxy->getSectors();
				foreach ($galSectors as $galSector) {
					if ($galSector->hasPort()) {
						$galSector->removePort();
					}
				}
				//get race for all ports
				while ($totalPorts > $assignedPorts) {
					//this adds extra ports until we reach the requested #
					$numRacePorts[array_rand($numRacePorts)]++;
					$assignedPorts++;
				}
				//iterate through levels 1-9 port
				foreach ($numLevelPorts as $portLevel => $numLevel) {
					//iterate once for each port of this level
					for ($j = 0; $j < $numLevel; $j++) {
						//get a sector for this port
						$galSector = findValidSector(
							$galSectors,
							fn(SmrSector $sector): bool => !$sector->hasPort() && !$sector->offersFederalProtection()
						);

						$raceID = array_rand($numRacePorts);
						$numRacePorts[$raceID]--;
						if ($numRacePorts[$raceID] == 0) {
							unset($numRacePorts[$raceID]);
						}
						$port = $galSector->createPort();
						$port->setRaceID($raceID);
						$port->upgradeToLevel($portLevel);
						$port->setCreditsToDefault();
					}
				}
				SmrPort::savePorts();
				$message = '<span class="green">Success</span> : Succesfully added ports.';
			} else {
				$message = '<span class="red">Error: Your port race distribution must equal 100!</span>';
			}
		} else {
			throw new Exception('Unknown submit: ' . $submit);
		}

		(new EditGalaxy($this->gameID, $this->galaxyID, $message))->go();
	}

}
