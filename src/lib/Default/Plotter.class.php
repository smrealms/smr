<?php declare(strict_types=1);

class Plotter {

	public static function getX(string $xType, int|string $X, int $gameID, SmrPlayer $player = null) : mixed {
		// Special case for Location categories (i.e. Bar, HQ, SafeFed)
		if (!is_numeric($X)) {
			if ($xType != 'Locations') {
				throw new Exception('Non-numeric X only exists for Locations');
			}
			return $X;
		}

		// In all other cases, X is a numeric ID
		$X = (int)$X;

		// Helper function for plots to trade goods
		$getGoodWithTransaction = function(int $goodID) use ($xType, $player) {
			$good = Globals::getGood($goodID);
			if (isset($player) && !$player->meetsAlignmentRestriction($good['AlignRestriction'])) {
				create_error('You do not have the correct alignment to see this good!');
			}
			$good['TransactionType'] = explode(' ', $xType)[0]; // use 'Buy' or 'Sell'
			return $good;
		};

		return match($xType) {
			'Technology' => Globals::getHardwareTypes($X),
			'Ships' => SmrShipType::get($X),
			'Weapons' => SmrWeaponType::getWeaponType($X),
			'Locations' => SmrLocation::getLocation($X),
			'Sell Goods', 'Buy Goods' => $getGoodWithTransaction($X),
			'Galaxies' => SmrGalaxy::getGalaxy($gameID, $X), // $X is the galaxyID
		};
	}

	/**
	 * Returns the shortest path from $sector to $x as a Distance object.
	 * The path is guaranteed reversible ($x -> $sector == $sector -> $x), which
	 * is not true for findDistanceToX. If $x is not a SmrSector, then this
	 * function does 2x the work.
	 */
	public static function findReversiblePathToX(mixed $x, SmrSector $sector, bool $useFirst, AbstractSmrPlayer $needsToHaveBeenExploredBy = null, AbstractSmrPlayer $player = null) : Distance {
		if ($x instanceof SmrSector) {

			// To ensure reversibility, always plot lowest to highest.
			$reverse = $sector->getSectorID() > $x->getSectorID();
			if ($reverse) {
				$start = $x;
				$end = $sector;
			} else {
				$start = $sector;
				$end = $x;
			}
			$path = Plotter::findDistanceToX($end, $start, $useFirst, $needsToHaveBeenExploredBy, $player);
			if ($path === false) {
				create_error('Unable to plot from ' . $sector->getSectorID() . ' to ' . $x->getSectorID() . '.');
			}
			// Reverse if we plotted $x -> $sector (since we want $sector -> $x)
			if ($reverse) {
				$path->reversePath();
			}

		} else {

			// At this point we don't know what sector $x will be at
			$path = Plotter::findDistanceToX($x, $sector, $useFirst, $needsToHaveBeenExploredBy, $player);
			if ($path === false) {
				create_error('Unable to find what you\'re looking for, it either hasn\'t been added to this game or you haven\'t explored it yet.');
			}
			// Now that we know where $x is, make sure path is reversible
			// (i.e. start sector < end sector)
			if ($path->getEndSectorID() < $sector->getSectorID()) {
				$path = Plotter::findDistanceToX($sector, $path->getEndSector(), true);
				$path->reversePath();
			}

		}
		return $path;
	}

	/**
	 * Returns the shortest path from $sector to $x as a Distance object.
	 * $x can be any type implemented by SmrSector::hasX or the string 'Distance'.
	 * The resulting path prefers neighbors in their order in SmrSector->links,
	 * (i.e. up, down, left, right).
	 */
	public static function findDistanceToX(mixed $x, SmrSector $sector, bool $useFirst, AbstractSmrPlayer $needsToHaveBeenExploredBy = null, AbstractSmrPlayer $player = null, int $distanceLimit = 10000, int $lowLimit = 0, int $highLimit = 100000) : Distance|array|false {
		$warpAddIndex = TURNS_WARP_SECTOR_EQUIVALENCE - 1;

		$checkSector = $sector;
		$gameID = $sector->getGameID();
		$distances = array();
		$sectorsTravelled = 0;
		$visitedSectors = array();
		$visitedSectors[$checkSector->getSectorID()] = true;
		if ($x == 'Distance') {
			$distances[0][$checkSector->getSectorID()] = new Distance($gameID, $checkSector->getSectorID());
		}

		$distanceQ = array();
		for ($i = 0; $i <= TURNS_WARP_SECTOR_EQUIVALENCE; $i++) {
			$distanceQ[] = array();
		}
		//Warps first as a slight optimisation due to how visitedSectors is set.
		if ($checkSector->hasWarp() === true) {
			$d = new Distance($gameID, $checkSector->getSectorID());
			$d->addWarpToPath($checkSector->getWarp(), $checkSector->getSectorID());
			$distanceQ[$warpAddIndex][] = $d;
		}
		foreach ($checkSector->getLinks() as $nextSector) {
			if ($nextSector !== 0) {
				$visitedSectors[$nextSector] = true;
				$d = new Distance($gameID, $checkSector->getSectorID());
				$d->addToPath($nextSector);
				$distanceQ[0][] = $d;
			}
		}
		$maybeWarps = 0;
		while ($maybeWarps <= TURNS_WARP_SECTOR_EQUIVALENCE) {
			$sectorsTravelled++;
			if ($sectorsTravelled > $distanceLimit) {
				return $distances;
			}
			if ($x == 'Distance') {
				$distances[$sectorsTravelled] = array();
			}
			$distanceQ[] = array();
			if (count($q = array_shift($distanceQ)) === 0) {
				$maybeWarps++;
				continue;
			}
			$maybeWarps = 0;
			while (($distance = array_shift($q)) !== null) {
				$checkSectorID = $distance->getEndSectorID();
				$visitedSectors[$checkSectorID] = true; // This is here for warps, because they are delayed visits if we set this before the actual visit we'll get sectors marked as visited long before they are actually visited - causes problems when it's quicker to walk to the warp exit than to warp there.
																// We still need to mark walked sectors as visited before we go to each one otherwise we get a huge number of paths being checked twice (up then left, left then up are essentially the same but if we set up-left as visited only when we actually check it then it gets queued up twice - nasty)
				if ($checkSectorID >= $lowLimit && $checkSectorID <= $highLimit) {
					$checkSector = SmrSector::getSector($gameID, $checkSectorID);
					if ($x == 'Distance') {
						$distances[$sectorsTravelled][$checkSector->getSectorID()] = $distance;
					} elseif (($needsToHaveBeenExploredBy === null || $needsToHaveBeenExploredBy->hasVisitedSector($checkSector->getSectorID())) === true
							&& $checkSector->hasX($x, $player) === true) {
						if ($useFirst === true) {
							return $distance;
						}
						$distances[$checkSector->getSectorID()] = $distance;
					}
					//Warps first as a slight optimisation due to how visitedSectors is set.
					if ($checkSector->hasWarp() === true) {
						if (!isset($visitedSectors[$checkSector->getWarp()])) {
							$cloneDistance = clone($distance);
							$cloneDistance->addWarpToPath($checkSector->getWarp(), $checkSector->getSectorID());
							$distanceQ[$warpAddIndex][] = $cloneDistance;
						}
					}
					foreach ($checkSector->getLinks() as $nextSector) {
						if (!isset($visitedSectors[$nextSector])) {
							$visitedSectors[$nextSector] = true;

							$cloneDistance = clone($distance);
							$cloneDistance->addToPath($nextSector);
							$distanceQ[0][] = $cloneDistance;
						}
					}
				}
			}
		}
		if ($useFirst === true) {
			$return = false;
			return $return;
		}
		return $distances;
	}

	public static function calculatePortToPortDistances(array $sectors, int $distanceLimit = 10000, int $lowLimit = 0, int $highLimit = 100000) : array {
		$distances = array();
		foreach ($sectors as $sec) {
			if ($sec !== null) {
				if ($sec->getSectorID() >= $lowLimit && $sec->getSectorID() <= $highLimit) {
					if ($sec->hasPort() === true) {
						$distances[$sec->getSectorID()] = self::findDistanceToOtherPorts($sec, $distanceLimit, $lowLimit, $highLimit);
					}
				}
			}
		}
		return $distances;
	}

	public static function findDistanceToOtherPorts(SmrSector $sector, int $distanceLimit = 10000, int $lowLimit = 0, int $highLimit = 100000) : array|false {
		return self::findDistanceToX('Port', $sector, false, null, null, $distanceLimit, $lowLimit, $highLimit);
	}
}

