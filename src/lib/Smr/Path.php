<?php declare(strict_types=1);

/**
 * Class used to keep track of paths between two sectors.
 * Used by the Plotter class to store the state of a plotted course.
 */
class Distance {
	private int $gameID;
	private int $distance = -1; //First sector added will be the start and a distance of 0
	private int $numWarps = 0;
	private array $path = [];
	private array $warpMap = [];

	public function __construct(int $gameID, int $_startSectorId) {
		$this->gameID = $gameID;
		$this->addToPath($_startSectorId);
	}

	protected function incrementDistance() : void {
		$this->distance++;
	}

	protected function incrementNumWarps() : void {
		$this->numWarps++;
	}

	public function getDistance() : int {
		return $this->distance;
	}

	public function getTotalSectors() : int {
		return $this->getDistance() + $this->getNumWarps();
	}

	public function getNumWarps() : int {
		return $this->numWarps;
	}

	public function getTurns() : int {
		return $this->distance * TURNS_PER_SECTOR + $this->numWarps * TURNS_PER_WARP;
	}

	public function getRelativeDistance() : int {
		return $this->distance + $this->numWarps * TURNS_WARP_SECTOR_EQUIVALENCE;
	}

	public function getEndSectorID() : int {
		return $this->path[count($this->path) - 1];
	}

	public function getEndSector() : SmrSector {
		return SmrSector::getSector($this->gameID, $this->getEndSectorID());
	}

	public function getPath() : array {
		return $this->path;
	}

	// NOTE: this assumes 2-way warps
	public function reversePath() : void {
		$this->path = array_reverse($this->path);
		$this->warpMap = array_flip($this->warpMap);
	}

	public function addToPath(int $nextSector) : void {
		$this->incrementDistance();
		$this->path[] = $nextSector;
	}

	public function addWarpToPath(int $sectorAfterWarp, int $sectorBeforeWarp) : void {
		$this->incrementNumWarps();
		$this->path[] = $sectorAfterWarp;
		$this->warpMap[$sectorBeforeWarp] = $sectorAfterWarp;
	}

	public function getNextOnPath() : int {
		return $this->path[0];
	}

	public function followPath() : void {
		$nextSectorID = array_shift($this->path);
		if (in_array($nextSectorID, $this->warpMap)) {
			$this->numWarps--;
		} else {
			$this->distance--;
		}
	}

	public function removeStart() : int {
		return array_shift($this->path);
	}

	public function isInPath(int $sectorID) : bool {
		return in_array($sectorID, $this->getPath());
	}

	/**
	 * If the given sector is in the path, then return the segment
	 * of the path that comes after the given sector.
	 */
	public function skipToSector(int $sectorID) : self {
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
