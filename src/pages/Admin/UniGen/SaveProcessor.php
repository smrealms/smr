<?php declare(strict_types=1);

use Smr\Exceptions\UserError;
use Smr\PlanetTypes\PlanetType;
use Smr\Race;
use Smr\Request;
use Smr\TransactionType;

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

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();

		$submit = Request::getVar('submit');
		$var['submit'] = null; // clear if set

		if ($submit == 'Create Galaxies') {
			for ($i = 1; $i <= $var['num_gals']; $i++) {
				$galaxy = SmrGalaxy::createGalaxy($var['game_id'], $i);
				$galaxy->setName(Request::get('gal' . $i));
				$galaxy->setWidth(Request::getInt('width' . $i));
				$galaxy->setHeight(Request::getInt('height' . $i));
				$galaxy->setGalaxyType(Request::get('type' . $i));
				$galaxy->setMaxForceTime(IFloor(Request::getFloat('forces' . $i) * 3600));
			}
			// Workaround for SmrGalaxy::getStartSector depending on all other galaxies
			SmrGalaxy::saveGalaxies();
			$galaxies = SmrGalaxy::getGameGalaxies($var['game_id'], true);
			foreach ($galaxies as $galaxy) {
				$galaxy->generateSectors();
			}
			SmrSector::saveSectors();
			$var['message'] = '<span class="green">Success</span> : Succesfully created galaxies.';
		} elseif ($submit == 'Redo Connections') {
			$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
			$connectivity = Request::getFloat('connect');
			if (!$galaxy->setConnectivity($connectivity)) {
				$var['message'] = '<span class="red">Error</span> : Regenerating connections failed.';
			} else {
				$var['message'] = '<span class="green">Success</span> : Regenerated connectivity with ' . $connectivity . '% target.';
			}
			SmrSector::saveSectors();
		} elseif ($submit == 'Toggle Link') {
			$linkSector = SmrSector::getSector($var['game_id'], Request::getInt('SectorID'));
			$linkSector->toggleLink(Request::get('Dir'));
			SmrSector::saveSectors();
		} elseif ($submit == 'Create Locations') {
			$galSectors = SmrSector::getGalaxySectors($var['game_id'], $var['gal_on']);
			foreach ($galSectors as $galSector) {
				$galSector->removeAllLocations();
			}
			foreach (SmrLocation::getAllLocations($var['game_id']) as $location) {
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
			$var['message'] = '<span class="green">Success</span> : Succesfully added locations.';
		} elseif ($submit == 'Create Warps') {
			//get all warp info from all gals, some need to be removed, some need to be added
			$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
			$galSectors = $galaxy->getSectors();
			//get totals
			foreach ($galSectors as $galSector) {
				if ($galSector->hasWarp()) {
					$galSector->removeWarp();
				}
			}
			//iterate over all the galaxies
			$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
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
			$var['message'] = '<span class="green">Success</span> : Succesfully added warps.';
		} elseif ($submit == 'Create Planets') {
			$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
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
			$var['message'] = '<span class="green">Success</span> : Succesfully added planets.';
		} elseif ($submit == 'Create Ports') {
			$numLevelPorts = [];
			$maxPortLevel = SmrPort::getMaxLevelByGame($var['game_id']);
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
				$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
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
				$var['message'] = '<span class="green">Success</span> : Succesfully added ports.';
			} else {
				$var['message'] = '<span class="red">Error: Your port race distribution must equal 100!</span>';
			}
		} elseif ($submit == 'Edit Sector') {
			$editSector = SmrSector::getSector($var['game_id'], $var['sector_edit']);

			//update planet
			$planetTypeID = Request::getInt('plan_type');
			if ($planetTypeID == 0) {
				$editSector->removePlanet();
			} elseif (!$editSector->hasPlanet()) {
				$editSector->createPlanet($planetTypeID);
			} else {
				$editSector->getPlanet()->setTypeID($planetTypeID);
			}

			//update port
			$portLevel = Request::getInt('port_level');
			if ($portLevel > 0) {
				if (!$editSector->hasPort()) {
					$port = $editSector->createPort();
				} else {
					$port = $editSector->getPort();
				}
				$port->setRaceID(Request::getInt('port_race'));
				if ($port->getLevel() != $portLevel) {
					$port->upgradeToLevel($portLevel);
					$port->setCreditsToDefault();
				} elseif (Request::has('select_goods')) {
					// Only set the goods manually if the level hasn't changed
					$goods = [];
					foreach (array_keys(Globals::getGoods()) as $goodID) {
						$trans = Request::get('good' . $goodID);
						if ($trans != 'None') {
							$goods[$goodID] = TransactionType::from($trans);
						}
					}
					if (!$port->setPortGoods($goods)) {
						create_error('Invalid goods specified for this port level!');
					}
				}
				$port->update();
			} else {
				$editSector->removePort();
			}

			//update locations
			$locationsToAdd = [];
			for ($x = 0; $x < UNI_GEN_LOCATION_SLOTS; $x++) {
				if (Request::getInt('loc_type' . $x) != 0) {
					$locationTypeID = Request::getInt('loc_type' . $x);
					$locationsToAdd[$locationTypeID] = SmrLocation::getLocation($var['game_id'], $locationTypeID);
				}
			}
			$editSector->removeAllLocations();
			foreach ($locationsToAdd as $locationToAddID => $locationToAdd) {
				// Skip duplicate locations
				if (!$editSector->hasLocation($locationToAddID)) {
					if (Request::has('add_linked_locs')) {
						addLocationToSector($locationToAdd, $editSector);
					} else {
						$editSector->addLocation($locationToAdd);
					}
				}
			}

			// update warp
			$warpSectorID = Request::getInt('warp');
			if ($warpSectorID > 0) {
				$warp = SmrSector::getSector($var['game_id'], $warpSectorID);
				if ($editSector->equals($warp)) {
					create_error('We do not allow any sector to warp to itself!');
				}
				// Removing warps first may do extra work, but is logically simpler
				$warp->removeWarp();
				$editSector->removeWarp();
				$editSector->setWarp($warp);
			} else {
				$editSector->removeWarp();
			}
			$var['message'] = '<span class="green">Success</span> : Succesfully edited sector.';
			SmrSector::saveSectors();
		}

		Page::create($var['forward_to'], $var)->go();
