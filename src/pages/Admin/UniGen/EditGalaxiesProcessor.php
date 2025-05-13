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
		private readonly int $galaxyID, // for back button only
	) {}

	public function build(Account $account): never {
		$game = Game::getGame($this->gameID);
		$galaxies = $game->getGalaxies();

		// Modify the galaxy properties
		foreach ($galaxies as $i => $galaxy) {
			$galaxy->setName(Request::get('gal' . $i));
			$galaxy->setGalaxyType(Request::get('type' . $i));
			$galaxy->setMaxForceTime(IFloor(Request::getFloat('forces' . $i) * 3600));
		}

		// Change galaxy dimensions (if game isn't enabled yet)
		$resized = false;
		if (!$game->isEnabled()) {
			$newSizes = [];
			foreach ($galaxies as $i => $galaxy) {
				$newSizes[$i] = [
					'Width' => Request::getInt('width' . $i),
					'Height' => Request::getInt('height' . $i),
				];
			}
			$resized = self::resizeGalaxies($this->gameID, $newSizes);
		}

		Galaxy::saveGalaxies();
		Sector::saveSectors();

		$message = '<span class="green">SUCCESS: </span>Edited galaxies (sizes' . ($resized ? ' ' : ' NOT ') . 'changed).';
		$container = new EditGalaxy($this->gameID, $this->galaxyID, $message);
		$container->go();
	}

	/**
	 * @param array<int, array{Width: int, Height: int}> $newSizes
	 * @return bool Return true if any galaxy sizes changed
	 */
	public static function resizeGalaxies(int $gameID, array $newSizes): bool {
		$db = Database::getInstance();
		$galaxies = Galaxy::getGameGalaxies($gameID);

		// Store the old sizes and then resize each galaxy
		$origGals = [];
		foreach ($galaxies as $i => $galaxy) {
			$origGals[$i] = [
				'Width' => $galaxy->getWidth(),
				'Height' => $galaxy->getHeight(),
			];
			if (isset($newSizes[$i])) {
				$galaxy->setWidth($newSizes[$i]['Width']);
				$galaxy->setHeight($newSizes[$i]['Height']);
			}
		}

		// Early return if no galaxy dimensions are modified
		$galaxySizesUnchanged = true;
		foreach ($galaxies as $i => $galaxy) {
			if ($galaxy->getWidth() !== $origGals[$i]['Width'] || $galaxy->getHeight() !== $origGals[$i]['Height']) {
				$galaxySizesUnchanged = false;
				break;
			}
		}
		if ($galaxySizesUnchanged) {
			return false;
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
						$db->delete('sector', $delSector->SQLID);
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
		$db->update(
			'sector',
			['warp' => 0],
			['game_id' => $gameID],
		);

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
				if ($oldID === false || $oldID === $newID) {
					unset($needsUpdate[$newID]);
					continue;
				}

				// If the oldID still exists, then we have to defer shifting until
				// this destination has been vacated.
				if (in_array($newID, $needsUpdate, true)) {
					continue;
				}

				// Else we are ready to shift from oldID to newID
				$oldSector = Sector::getSector($gameID, $oldID);
				$data = [
					'sector_id' => $db->escapeNumber($newID),
				];

				if ($oldSector->hasPlanet()) {
					$db->update('planet', $data, $oldSector->SQLID);
					$db->update('planet_has_building', $data, $oldSector->SQLID);
					$db->update('planet_has_cargo', $data, $oldSector->SQLID);
					$db->update('planet_has_weapon', $data, $oldSector->SQLID);
				}

				if ($oldSector->hasPort()) {
					$db->update('port', $data, $oldSector->SQLID);
					$db->update('port_has_goods', $data, $oldSector->SQLID);
				}

				if ($oldSector->hasLocation()) {
					$db->update('location', $data, $oldSector->SQLID);
				}

				$db->update('sector', $data, $oldSector->SQLID);
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
				$newWarpID = array_search($oldWarpID, $sectorMap, true);
				if ($newWarpID === false) {
					throw new Exception('Warp sector unexpectedly missing from mapping: ' . $oldWarpID);
				}
				$newSector->setWarp(Sector::getSector($gameID, $newWarpID));
			}
		}

		return true;
	}

}
