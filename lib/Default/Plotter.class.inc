<?php
require_once(get_file_loc('SmrSector.class.inc'));
class Plotter {
	public function __construct() {
	}

	public static function getX($xType, $X, $gameID, $player=null) {
		switch($xType) {
			case 'Technology':
				return Globals::getHardwareTypes($X);
			case 'Ships':
				return AbstractSmrShip::getBaseShip(Globals::getGameType($gameID),$X);
			case 'Weapons':
				return SmrWeapon::getWeapon(Globals::getGameType($gameID),$X);
			case 'Locations':
				if(is_numeric($X)) {
					return SmrLocation::getLocation($X);
				}
				return $X;
			case 'Sell Goods':
			case 'Buy Goods':
				// $X is the good ID
				$good = Globals::getGood($X);
				if (isset($player) && !$player->meetsAlignmentRestriction($good['AlignRestriction'])) {
					create_error('You do not have the correct alignment to see this good!');
				}
				$good['TransactionType'] = explode(' ', $xType)[0];
				return $good;
			case 'Galaxies':
				// $X is the galaxyID
				return SmrGalaxy::getGalaxy($gameID, $X);
			default:
				return false;
		}
	}

	/**
	 * Returns the shortest path from $sector to $x as a Distance object.
	 * The path is guaranteed reversible ($x -> $sector == $sector -> $x), which
	 * is not true for findDistanceToX. If $x is not a SmrSector, then this
	 * function does 2x the work.
	 */
	public static function findReversiblePathToX($x, SmrSector $sector, $useFirst, AbstractSmrPlayer $needsToHaveBeenExplored=null, AbstractSmrPlayer $player=null) {
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
			$path = Plotter::findDistanceToX($end, $start, $useFirst,
			                                 isset($needsToHaveBeenExploredBy) ? $needsToHavebeenExploredBy : null,
			                                 isset($player) ? $player : null);
			if ($path===false) {
				create_error('Unable to plot from '.$sector->getSectorID().' to '.$x->getSectorID().'.');
			}
			// Reverse if we plotted $x -> $sector (since we want $sector -> $x)
			if ($reverse) {
				$path->reversePath();
			}

		} else {

			// At this point we don't know what sector $x will be at
			$path = Plotter::findDistanceToX($x, $sector, $useFirst,
			                                 isset($needsToHaveBeenExploredBy) ? $needsToHavebeenExploredBy : null,
			                                 isset($player) ? $player : null);
			if ($path===false) {
				create_error('Unable to find what you\'re looking for, it either hasn\'t been added to this game or you haven\'t explored it yet.');
			}
			// Now that we know where $x is, make sure path is reversible
			// (i.e. start sector < end sector)
			if ($path->getEndSectorID() < $sector->getSectorID()) {
				$path =& Plotter::findDistanceToX($sector, $path->getEndSector(), true);
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
	public static function &findDistanceToX($x, SmrSector &$sector, $useFirst, AbstractSmrPlayer $needsToHaveBeenExploredBy=null, AbstractSmrPlayer $player=null, $distanceLimit=10000, $lowLimit=0, $highLimit=100000) {
		$warpAddIndex = TURNS_WARP_SECTOR_EQUIVALENCE-1;

		$checkSector =& $sector;
		$gameID = $sector->getGameID();
		$distances = array();
		$sectorsTravelled = 0;
		$visitedSectors = array();
		$visitedSectors[$checkSector->getSectorID()] = true;
		if($x=='Distance')
			$distances[0][$checkSector->getSectorID()] = new Distance($gameID,$checkSector->getSectorID());

		$distanceQ = array();
		for($i=0;$i<=TURNS_WARP_SECTOR_EQUIVALENCE;$i++)
			$distanceQ[] = array();
		//Warps first as a slight optimisation due to how visitedSectors is set.
		if($checkSector->hasWarp()===true) {
			$d = new Distance($gameID,$checkSector->getSectorID());
			$d->addWarpToPath($checkSector->getWarp(), $checkSector->getSectorID());
			$distanceQ[$warpAddIndex][] = $d;
		}
		foreach ($checkSector->getLinks() as $nextSector) {
			if($nextSector!==0) {
				$visitedSectors[$nextSector] = true;
				$d = new Distance($gameID,$checkSector->getSectorID());
				$d->addToPath($nextSector);
				$distanceQ[0][] = $d;
			}
		}
		$maybeWarps=0;
		while ($maybeWarps<=TURNS_WARP_SECTOR_EQUIVALENCE) {
			$sectorsTravelled++;
			if ($sectorsTravelled > $distanceLimit)
				return $distances;
			if($x=='Distance') {
				$distances[$sectorsTravelled] = array();
			}
			$distanceQ[] = array();
			if(count($q = array_shift($distanceQ))===0) {
				$maybeWarps++;
				continue;
			}
			$maybeWarps=0;
			while (($distance = array_shift($q))!==null) {
				$checkSectorID = $distance->getEndSectorID();
				$visitedSectors[$checkSectorID] = true; // This is here for warps, because they are delayed visits if we set this before the actual visit we'll get sectors marked as visited long before they are actually visited - causes problems when it's quicker to walk to the warp exit than to warp there.
																// We still need to mark walked sectors as visited before we go to each one otherwise we get a huge number of paths being checked twice (up then left, left then up are essentially the same but if we set up-left as visited only when we actually check it then it gets queued up twice - nasty)
				if ($checkSectorID >= $lowLimit && $checkSectorID <= $highLimit) {
					$checkSector =& SmrSector::getSector($gameID,$checkSectorID);
					if($x=='Distance') {
						$distances[$sectorsTravelled][$checkSector->getSectorID()] = $distance;
					}
					else if (($needsToHaveBeenExploredBy===null||$needsToHaveBeenExploredBy->hasVisitedSector($checkSector->getSectorID()))===true
							&& $checkSector->hasX($x,$player)===true) {
						if($useFirst===true)
							return $distance;
						$distances[$checkSector->getSectorID()] = $distance;
					}
					//Warps first as a slight optimisation due to how visitedSectors is set.
					if($checkSector->hasWarp()===true) {
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
		if($useFirst===true) {
			$return = false;
			return $return;
		}
		return $distances;
	}
	
	public static function &calculatePortToPortDistances(array &$sectors, $distanceLimit = 10000, $lowLimit = 0, $highLimit = 100000) {
		$distances = array();
		foreach($sectors as &$sec) {
			if ($sec !== null) {
				if ($sec->getSectorID() >= $lowLimit && $sec->getSectorID() <= $highLimit) {
					if ($sec->hasPort()===true) {
						$distances[$sec->getSectorID()] =& self::findDistanceToOtherPorts($sec, $distanceLimit, $lowLimit, $highLimit);
					}
				}
			}
		} unset($sec);
		return $distances;
	}

	public static function &findDistanceToOtherPorts(SmrSector &$sector, $distanceLimit = 10000, $lowLimit = 0, $highLimit = 100000) {
		return self::findDistanceToX('Port', $sector, false, null, null, $distanceLimit, $lowLimit, $highLimit, $distanceLimit);
	}
}

class Distance {
	private $gameID;
	private $distance = -1; //First sector added will be the start and a distance of 0
	private $numWarps = 0;
	private $path = array();
	private $warpMap = array();

	public function __construct($gameID,$_startSectorId) {
		$this->gameID = $gameID;
		$this->addToPath($_startSectorId);
	}

//	public function __construct($_distance, $_numWarps, array $_path) {
//		$this->distance = $_distance;
//		$this->numWarps = $_numWarps;
//		$this->path = $_path;
//	}

	protected function incrementDistance() {
		$this->distance++;
	}

	protected function incrementNumWarps() {
		$this->numWarps++;
	}

	public function getDistance() {
		return $this->distance;
	}

	public function getTotalSectors() {
		return $this->getDistance()+$this->getNumWarps();
	}

	public function getNumWarps() {
		return $this->numWarps;
	}

	public function getTurns() {
		return $this->distance * TURNS_PER_SECTOR + $this->numWarps * TURNS_PER_WARP;
	}

	public function getRelativeDistance() {
		return $this->distance + $this->numWarps * TURNS_WARP_SECTOR_EQUIVALENCE;
	}

	/**
	 * @return the endSectorId
	 */
	public function getEndSectorID() {
		return $this->path[count($this->path)-1];
	}

	public function &getEndSector() {
		return SmrSector::getSector($this->gameID,$this->getEndSectorID());
	}

	/**
	 * @return the startSectorId
	 */
	public function getStartSectorId() {
		return $this->path[0];
	}

	public function compareTo(Distance &$d) {
		if ($this->equals($d)===true)
			return 0;
		else if ($d->getTurns() > $this->getTurns())
			return -1;
		return 1;
	}

	/**
	 * @return the path
	 */
	public function getPath() {
		return $this->path;
	}
	
	// NOTE: this assumes 2-way warps
	public function reversePath() {
		$this->path = array_reverse($this->path);
		$this->warpMap = array_flip($this->warpMap);
	}

	/**
	 * @param path
	 *		the path to set
	 */
	public function addToPath($nextSector) {
		$this->incrementDistance();
		$this->path[] = $nextSector;
	}
	
	public function addWarpToPath($sectorAfterWarp, $sectorBeforeWarp) {
		$this->incrementNumWarps();
		$this->path[] = $sectorAfterWarp;
		$this->warpMap[$sectorBeforeWarp] = $sectorAfterWarp;
	}
	
	public function getNextOnPath() {
		return $this->path[0];
	}
	
	public function followPath($isWarp=false) {
		if($isWarp===false)
			$this->distance--;
		else
			$this->numWarps--;
		return array_shift($this->path);
	}
	
	public function removeStart() {
		return array_shift($this->path);
	}
	
	public function isInPath($sectorID) {
		return in_array($sectorID,$this->getPath());
	}

	/**
	 * If the given sector is in the path, then return the segment
	 * of the path that comes after the given sector.
	 */
	public function skipToSector($sectorID) {
		$position = array_search($sectorID, $this->path);
		if ($position !== false) {
			// The resulting path does not include sectorID, i.e. (sectorID,end]
			$this->path = array_slice($this->path, $position + 1);
			$this->numWarps = count(array_intersect($this->path, array_values($this->warpMap)));
			$this->distance = count($this->path) - $this->numWarps;
			return $this;
		} else {
			throw new Exception('Cannot skip to sector not in path!');
		}
	}
}

?>
