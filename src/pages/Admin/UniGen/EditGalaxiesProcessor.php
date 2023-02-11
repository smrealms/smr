<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\Planet;
use Smr\Port;
use Smr\Request;
use Smr\Sector;

class EditGalaxiesProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$gameID = $this->gameID;
		$game = Game::getGame($gameID);
		$galaxies = $game->getGalaxies();

		// Save the original sizes for later processing
		$origGals = [];
		foreach ($galaxies as $i => $galaxy) {
			$origGals[$i] = [
				'Width' => $galaxy->getWidth(),
				'Height' => $galaxy->getHeight(),
			];
		}

		// Modify the galaxy properties
		foreach ($galaxies as $i => $galaxy) {
			$galaxy->setName(Request::get('gal' . $i));
			$galaxy->setGalaxyType(Request::get('type' . $i));
			$galaxy->setMaxForceTime(IFloor(Request::getFloat('forces' . $i) * 3600));
			if (!$game->isEnabled()) {
				$galaxy->setWidth(Request::getInt('width' . $i));
				$galaxy->setHeight(Request::getInt('height' . $i));
			}
		}

		// Early return if no galaxy dimensions are modified
		$galaxySizesUnchanged = true;
		foreach ($galaxies as $i => $galaxy) {
			if ($galaxy->getWidth() != $origGals[$i]['Width'] || $galaxy->getHeight() != $origGals[$i]['Height']) {
				$galaxySizesUnchanged = false;
				break;
			}
		}
		if ($galaxySizesUnchanged) {
			Galaxy::saveGalaxies();
			$message = '<span class="green">SUCCESS: </span>Edited galaxies (sizes unchanged).';
			$container = new EditGalaxy($this->gameID, $this->galaxyID, $message);
			$container->go();
		}

		// *** BEGIN GALAXY DIMENSION MODIFICATION! ***
		// Warning: This changes primary keys for several tables, which needs to be
		// done carefully. It also interacts with the caches in unexpected ways.
		// *** BEGIN GALAXY DIMENSION MODIFICATION! ***

		// Efficiently construct the caches before proceeding
		// NOTE: these will be associated with the old sector IDs, so the caches
		// will need to be cleared afterwards.
		foreach ($galaxies as $galaxy) {
			$galaxy->getSectors();
			$galaxy->getPorts();
			$galaxy->getLocations();
			$galaxy->getPlanets();
		}

		// Determine the mapping from old to new sector IDs
		$newID = 0;
		$oldID = 0;
		$sectorMap = [];
		foreach ($galaxies as $i => $galaxy) {
			$maxRows = max($galaxy->getHeight(), $origGals[$i]['Height']);
			$maxCols = max($galaxy->getWidth(), $origGals[$i]['Width']);
			for ($row = 0; $row < $maxRows; $row++) {
				for ($col = 0; $col < $maxCols; $col++) {
					$oldExists = ($row < $origGals[$i]['Height'] && $col < $origGals[$i]['Width']);
					$newExists = ($row < $galaxy->getHeight() && $col < $galaxy->getWidth());

					if ($oldExists && $newExists) {
						$oldID++;
						$newID++;
						$sectorMap[$newID] = $oldID;
					} elseif ($newExists) {
						$newID++;
						$sectorMap[$newID] = false;
					} elseif ($oldExists) {
						$oldID++;
						// Remove this sector and everything in it
						$delSector = Sector::getSector($gameID, $oldID);
						$delSector->removeAllFixtures();
						$db->write('DELETE FROM sector WHERE ' . Sector::SQL, $delSector->SQLID);
					}
				}
			}
		}

		// Save remaining old warps to re-add later, then clear all warp data.
		// This is necessary because we will be manually modifying sector IDs.
		$oldWarps = [];
		foreach ($galaxies as $galaxy) {
			foreach ($galaxy->getSectors() as $galSector) {
				if ($galSector->hasWarp()) {
					$oldWarps[$galSector->getSectorID()] = $galSector->getWarp();
				}
			}
		}
		$db->write('UPDATE sector SET warp = 0 WHERE game_id = :game_id', [
			'game_id' => $db->escapeNumber($gameID),
		]);

		// Many sectors will have their IDs shifted up or down, so we need to modify
		// the primary keys for the sector table as well as planets, ports, etc.
		// We have to do this in a loop to ensure that the new sector ID will not
		// collide with an old sector ID that hasn't been shifted yet (because we
		// may be both adding and removing sectors).
		//
		// NOTE: We have already accounted for collisions from removing sectors by
		// deleting all fixtures from sectors that will no longer exist.
		$needsUpdate = $sectorMap;
		while ($needsUpdate) {
			foreach ($needsUpdate as $newID => $oldID) {
				// If sector is new or has the same ID, then no shifting is necessary
				if ($oldID === false || $oldID == $newID) {
					unset($needsUpdate[$newID]);
					continue;
				}

				// If the oldID still exists, then we have to defer shifting until
				// this destination has been vacated.
				if (array_search($newID, $needsUpdate)) {
					continue;
				}

				// Else we are ready to shift from oldID to newID
				$oldSector = Sector::getSector($gameID, $oldID);
				$SQL = 'SET sector_id = :new_sector_id WHERE ' . Sector::SQL;
				$params = [
					'new_sector_id' => $db->escapeNumber($newID),
					...$oldSector->SQLID,
				];

				if ($oldSector->hasPlanet()) {
					$db->write('UPDATE planet ' . $SQL, $params);
					$db->write('UPDATE planet_has_building ' . $SQL, $params);
					$db->write('UPDATE planet_has_cargo ' . $SQL, $params);
					$db->write('UPDATE planet_has_weapon ' . $SQL, $params);
				}

				if ($oldSector->hasPort()) {
					$db->write('UPDATE port ' . $SQL, $params);
					$db->write('UPDATE port_has_goods ' . $SQL, $params);
				}

				if ($oldSector->hasLocation()) {
					$db->write('UPDATE location ' . $SQL, $params);
				}

				$db->write('UPDATE sector ' . $SQL, $params);
				unset($needsUpdate[$newID]);
			}
		}

		// Clear all the caches, since they are associated with the old IDs.
		// NOTE: We can't re-initialize the cache here because the sectors
		// still have the wrong galaxy ID at this point.
		Sector::clearCache();
		Port::clearCache();
		Planet::clearCache();
		Location::clearCache();

		// Create any new sectors that need to be made
		foreach ($sectorMap as $newID => $oldID) {
			if ($oldID === false) {
				Sector::createSector($gameID, $newID);
			}
		}

		// Finally, modify sector properties (galaxy ID, links, and warps)
		foreach ($sectorMap as $newID => $oldID) {
			$newSector = Sector::getSector($gameID, $newID);

			// Update the galaxy ID
			// NOTE: this must be done before Galaxy::getSectors is called
			foreach ($galaxies as $galaxy) {
				if ($galaxy->contains($newID)) {
					$newSector->setGalaxyID($galaxy->getGalaxyID());
				}
			}

			// Update the sector connections
			foreach (['Up', 'Down', 'Left', 'Right'] as $dir) {
				if ($oldID === false) {
					// No sector walls for newly added sectors
					$newSector->enableLink($dir);
				} else {
					// Toggle links twice to reset them (since this internally handles
					// the calculation of the neighboring sector IDs).
					$newSector->toggleLink($dir);
					$newSector->toggleLink($dir);
				}
			}

			// Update the warp
			if ($oldID !== false && isset($oldWarps[$oldID])) {
				$oldWarpID = $oldWarps[$oldID];
				$newWarpID = array_search($oldWarpID, $sectorMap);
				if ($newWarpID === false) {
					throw new Exception('Warp sector unexpectedly missing from mapping: ' . $oldWarpID);
				}
				$newSector->setWarp(Sector::getSector($gameID, $newWarpID));
			}
		}

		Galaxy::saveGalaxies();
		Sector::saveSectors();

		$message = '<span class="green">SUCCESS: </span>Edited galaxies (sizes have changed).';
		$container = new EditGalaxy($this->gameID, $this->galaxyID, $message);
		$container->go();
	}

}
