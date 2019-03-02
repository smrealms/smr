<?php

/**
 * Class used to keep track of paths between two sectors.
 * Used by the Plotter class to store the state of a plotted course.
 */
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
	 * @return integer
	 */
	public function getEndSectorID() {
		return $this->path[count($this->path)-1];
	}

	public function &getEndSector() {
		return SmrSector::getSector($this->gameID,$this->getEndSectorID());
	}

	public function compareTo(Distance $d) {
		if ($this->equals($d)===true)
			return 0;
		else if ($d->getTurns() > $this->getTurns())
			return -1;
		return 1;
	}

	/**
	 * @return array
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
	 * @param integer $nextSector
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

	public function followPath() {
		$nextSectorID = array_shift($this->path);
		if (in_array($nextSectorID, $this->warpMap)) {
			$this->numWarps--;
		} else {
			$this->distance--;
		}
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
