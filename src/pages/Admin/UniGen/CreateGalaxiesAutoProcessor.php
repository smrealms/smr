<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Account;
use Smr\Galaxy;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\PlanetTypes\PlanetType;
use Smr\Race;
use Smr\Sector;
use Smr\StdlibExtensions\InfiniteArrayIterator;

class CreateGalaxiesAutoProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameId,
	) {}

	public function build(Account $account): never {

		// Prepare locations
		//***********************************

		$locations = Location::getAllLocations($this->gameId);

		$ndz = $locations[LOCATION_NDZ];
		$ca = $locations[LOCATION_CA];
		$bdf = $locations[LOCATION_BDF];
		$uno = $locations[LOCATION_UNO];

		$fedHQ = $locations[LOCATION_FEDERAL_HQ];
		$ug = $locations[LOCATION_UNDERGROUND];

		$bankIter = new InfiniteArrayIterator(Location::getAllBanks());
		$barIter = new InfiniteArrayIterator(Location::getAllBars());

		$techShops = [
			$locations[LOCATION_ACCELERATED_SYSTEMS],
			$locations[LOCATION_ADVANCED_COMMUNICATIONS],
			$locations[LOCATION_HIDDEN_TECHNOLOGY],
			$locations[LOCATION_IMAGE_SYSTEMS],
			$locations[LOCATION_CRONE_DRONFUSION],
		];

		$shipShops = Location::getAllShipShops();
		$weaponShops = Location::getAllWeaponShops();

		// Remove special ship/weapon shops
		unset($weaponShops[RACE_WARS_WEAPONS]);
		unset($shipShops[RACE_WARS_SHIPS]);
		unset($shipShops[LOCATION_TEST_SHIPYARD]);
		foreach (Race::getPlayableIDs() as $raceId) {
			unset($shipShops[RACIAL_SHIPS + $raceId]);
		}
		foreach ([$fedHQ, $ug] as $loc) {
			foreach ($loc->getLinkedLocations() as $linkedLoc) {
				unset($weaponShops[$linkedLoc->getTypeID()]);
				unset($shipShops[$linkedLoc->getTypeID()]);
			}
		}

		// Shuffle shops so that pop gives a random one (array keys are lost)
		shuffle($shipShops);
		shuffle($weaponShops);

		$nextShipShop = function() use (&$shipShops): Location {
			$next = array_pop($shipShops);
			if ($next === null) {
				throw new Exception('No locations left in shop shop array');
			}
			return $next;
		};
		$nextWeaponShop = function() use (&$weaponShops): Location {
			$next = array_pop($weaponShops);
			if ($next === null) {
				throw new Exception('No locations left in weapon shop array');
			}
			return $next;
		};

		// Get a randomized pool of galaxy names
		$galaxyNames = CreateGalaxies::GALAXY_NAMES;
		shuffle($galaxyNames);

		$nextGalaxyName = function() use (&$galaxyNames): string {
			$next = array_pop($galaxyNames);
			if ($next === null) {
				return 'Unknown';
			}
			return $next;
		};

		// Create galaxies
		//***********************************

		// Create racial galaxies
		$galaxyId = 0;
		$racialGalaxies = [];
		foreach (Race::getPlayableNames() as $raceId => $raceName) {
			$galaxy = Galaxy::createGalaxy($this->gameId, ++$galaxyId);
			$galaxy->setName($raceName);
			$galaxy->setWidth(9);
			$galaxy->setHeight(9);
			$galaxy->setGalaxyType(Galaxy::TYPE_RACIAL);
			$galaxy->setMaxForceTime(3600 * 6); // 6 hours
			$racialGalaxies[$raceId] = $galaxy;
		}

		// Create hub galaxy (expects 8 playable races)
		$hubGalaxy = Galaxy::createGalaxy($this->gameId, ++$galaxyId);
		$hubGalaxy->setName('Nexus');
		$hubGalaxy->setWidth(3);
		$hubGalaxy->setHeight(3);
		$hubGalaxy->setGalaxyType(Galaxy::TYPE_NEUTRAL);
		$hubGalaxy->setMaxForceTime(0);

		// Create large neutral galaxy
		$neutralGalaxy = Galaxy::createGalaxy($this->gameId, ++$galaxyId);
		$neutralGalaxy->setName($nextGalaxyName());
		$neutralGalaxy->setWidth(15);
		$neutralGalaxy->setHeight(15);
		$neutralGalaxy->setGalaxyType(Galaxy::TYPE_NEUTRAL);
		$neutralGalaxy->setMaxForceTime(IRound(86400 * 3.5)); // 3.5 days

		// Create planet galaxies
		$numPlanetGals = 4;
		$planetGalaxies = [];
		for ($i = 0; $i < $numPlanetGals; $i++) {
			$galaxy = Galaxy::createGalaxy($this->gameId, ++$galaxyId);
			$galaxy->setName($nextGalaxyName());
			$galaxy->setWidth(rand(4, 6));
			$galaxy->setHeight(rand(4, 6));
			$galaxy->setGalaxyType(Galaxy::TYPE_PLANET);
			$galaxy->setMaxForceTime(86400 * 7); // 7 days
			$planetGalaxies[] = $galaxy;
		}

		//$numGalaxies = $galaxyId;
		Galaxy::saveGalaxies();

		// Set up racial galaxies
		//***********************************

		$getCenterSector = function(Galaxy $galaxy): Sector {
			$centerSectorId = intdiv($galaxy->getEndSector() + $galaxy->getStartSector(), 2);
			return Sector::getSector($this->gameId, $centerSectorId);
		};

		$hqSectors = [];
		foreach ($racialGalaxies as $raceId => $galaxy) {
			$galSectors = $galaxy->generateSectors();
			$galaxy->setConnectivity(78);

			// Set up center sector for the HQ placement with walls like:
			//  __| |__
			//  __   __
			//    | |
			$centerSector = $getCenterSector($galaxy);
			foreach (Sector::getLinkDirs() as $linkDir) {
				// No walls for the center sector (HQ)
				$centerSector->enableLink($linkDir);
				$neighbor = $centerSector->getNeighbourSector($linkDir);

				// No walls for the 2nd neighbor to avoid random dead-ends
				$nextNeighbor = $neighbor->getNeighbourSector($linkDir);
				foreach (Sector::getLinkDirs() as $linkDir2) {
					$nextNeighbor->enableLink($linkDir2);
				}
				$nextNeighbor->addLocation($ndz); // to prevent mine/scout choke points

				// Make neighbors tunnel sectors
				switch ($linkDir) {
					case 'Up':
					case 'Down':
						$neighbor->disableLink('Left');
						$neighbor->disableLink('Right');
						break;
					case 'Left':
					case 'Right':
						$neighbor->disableLink('Up');
						$neighbor->disableLink('Down');
						break;
					default:
						throw new Exception('Unhandled link dir: ' . $linkDir);
				}
			}

			// Add the HQ locations to center sector
			$hq = Location::getLocation($this->gameId, LOCATION_GROUP_RACIAL_HQS + $raceId);
			$centerSector->addLocation($hq);
			$centerSector->addLinkedLocations($hq);
			$hqSectors[] = $centerSector;

			// Add the rest of the locations
			$locsToAdd = [
				$bankIter->getAndAdvance(),
				$barIter->getAndAdvance(),
				$bdf,
				$uno,
				$nextShipShop(),
				$nextWeaponShop(),
			];
			foreach ($locsToAdd as $loc) {
				$locSector = SaveProcessor::findValidSector(
					$galSectors,
					fn(Sector $sector): bool => !$sector->hasLocation(),
				);
				$locSector->addLocation($loc);
			}

			// Add ports
			$totalPorts = IRound($galaxy->getSize() * 0.5);
			$fracRacePorts = [
				RACE_NEUTRAL => 0.1,
				$raceId => 0.9,
			];
			$fracLevelPorts = [
				2 => 0.1,
				5 => 0.1,
				6 => 0.2,
				8 => 0.3,
				9 => 0.3,
			];
			$fracToNum = fn(float $x): int => ICeil($x * $totalPorts);
			SaveProcessor::createPorts(
				$galaxy,
				numRacePorts: array_map($fracToNum, $fracRacePorts),
				numLevelPorts: array_map($fracToNum, $fracLevelPorts),
				removeExisting: false, // no need, galaxy should be empty
			);
		}

		// Set up hub galaxy
		//***********************************

		// Put walls around the edge of the galaxy like this:
		//  __ __ __
		// |        |
		// |        |
		// |__ __ __|
		$hubGalaxy->generateSectors();
		//$hubCenter = $getCenterSector($hubGalaxy);
		$hubSectors = $hubGalaxy->getMapSectors();
		foreach ($hubSectors as $row => $rowSectors) {
			foreach ($rowSectors as $col => $sector) {
				$pos = [$row, $col];
				$disableDirs = match ($pos) {
					[0, 0] => ['Up', 'Left'],
					[0, 1] => ['Up'],
					[0, 2] => ['Up', 'Right'],
					[1, 0] => ['Left'],
					[1, 1] => [],
					[1, 2] => ['Right'],
					[2, 0] => ['Down', 'Left'],
					[2, 1] => ['Down'],
					[2, 2] => ['Down', 'Right'],
					default => throw new Exception('Unhandled case: row=' . $row . ', col=' . $col),
				};
				foreach ($disableDirs as $dir) {
					$sector->disableLink($dir);
				}

				if ($pos !== [1, 1]) {
					// Warp to a racial HQ around the edges
					$hqSector = array_shift($hqSectors);
					if ($hqSector === null) {
						throw new Exception('Too many sectors in hub gal for HQ warps');
					}
					$sector->setWarp($hqSector);
					$sector->addLocation($ndz);
				} else {
					// Add Defense World in the center (for NHA)
					$sector->createPlanet(PlanetType::TYPE_DEFENSE);
					$sector->addLocation($uno); // for brave souls...?
				}
			}
		}

		// Set up large neutral galaxy
		//***********************************

		$sectors = $neutralGalaxy->generateSectors();
		$neutralGalaxy->setConnectivity(65);

		// Add warps to each racial
		foreach ($racialGalaxies as $raceId => $racialGalaxy) {
			$warpSector = SaveProcessor::findValidSector(
				$sectors,
				fn(Sector $sector): bool => !$sector->hasWarp(),
			);
			$destSector = SaveProcessor::findValidSector(
				$racialGalaxy->getSectors(),
				fn(Sector $sector): bool => !$sector->hasLocation() && !$sector->hasWarp(),
			);
			$warpSector->setWarp($destSector);

			// Add NDZ to both sides
			$warpSector->addLocation($ndz);
			$destSector->addLocation($ndz);
		}

		// Add locations
		$locsToAdd = [
			$bankIter->getAndAdvance(),
			$bankIter->getAndAdvance(),
			$barIter->getAndAdvance(),
			$barIter->getAndAdvance(),
			$uno,
			$uno,
			$uno,
			$uno,
			$bdf,
			$bdf,
			$ca,
			$fedHQ, // has linked locs
			$ug, // has linked locs
			...$shipShops,
			...$weaponShops,
			...$techShops,
		];
		foreach ($locsToAdd as $loc) {
			$locSector = SaveProcessor::findValidSector(
				$sectors,
				fn(Sector $sector): bool => !$sector->hasLocation(),
			);
			$locSector->addLocation($loc);
			$locSector->addLinkedLocations($loc);
		}

		// Add ports
		$totalPorts = IRound($neutralGalaxy->getSize() * 0.33);
		$fracRacePorts = [
			RACE_NEUTRAL => 0.4,
		];
		foreach (Race::getPlayableIDs() as $raceId) {
			$fracRacePorts[$raceId] = 0.075;
		}
		$fracLevelPorts = [
			2 => 0.1,
			5 => 0.1,
			6 => 0.2,
			8 => 0.3,
			9 => 0.3,
		];
		$fracToNum = fn(float $x): int => ICeil($x * $totalPorts);
		SaveProcessor::createPorts(
			$neutralGalaxy,
			numRacePorts: array_map($fracToNum, $fracRacePorts),
			numLevelPorts: array_map($fracToNum, $fracLevelPorts),
			removeExisting: false, // no need, galaxy should be empty
		);

		// Set up planet galaxies
		//***********************************

		foreach ($planetGalaxies as $galaxy) {
			$sectors = $galaxy->generateSectors();
			$galaxy->setConnectivity(70);

			// Add warps to neutral
			$warpSector = array_rand_value($sectors);
			$destSector = SaveProcessor::findValidSector(
				$neutralGalaxy->getSectors(),
				fn(Sector $sector): bool => !$sector->hasLocation() && !$sector->hasWarp(),
			);
			$warpSector->setWarp($destSector);

			// Add bank/bar
			$locsToAdd = [
				$bankIter->getAndAdvance(),
				$barIter->getAndAdvance(),
			];
			foreach ($locsToAdd as $loc) {
				$locSector = SaveProcessor::findValidSector(
					$sectors,
					fn(Sector $sector): bool => !$sector->hasLocation() && !$sector->hasWarp(),
				);
				$locSector->addLocation($loc);
			}

			// Add planets
			$planetTypes = [
				PlanetType::TYPE_TERRAN,
				PlanetType::TYPE_DWARF,
				array_rand_value([PlanetType::TYPE_ARID, PlanetType::TYPE_PROTO]),
				array_rand_value([PlanetType::TYPE_ARID, PlanetType::TYPE_PROTO]),
			];
			foreach ($planetTypes as $planetType) {
				$sector = SaveProcessor::findValidSector(
					$sectors,
					fn(Sector $sector): bool => !$sector->hasLocation() && !$sector->hasWarp() && !$sector->hasPlanet(),
				);
				$sector->createPlanet($planetType);
			}

		}

		Sector::saveSectors();

		$message = '<span class="green">Success</span> : Succesfully created galaxies.';
		$container = new EditGalaxy(canEdit: true, gameID: $this->gameId, message: $message);
		$container->go();
	}

}
