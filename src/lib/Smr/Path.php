<?php declare(strict_types=1);

namespace Smr;

use Exception;

/**
 * Class used to keep track of paths between two sectors.
 * Used by the Plotter class to store the state of a plotted course.
 */
class Path {

	/** @var array<int, int> */
	private array $path;
	private int $numWarps = 0;
	/** @var array<int, int> */
	private array $warpMap = [];

	public function __construct(int $startSectorID) {
		$this->path = [$startSectorID];
	}

	/**
	 * Returns the number of sectors to get to the end of the path
	 * (does not include the start sector).
	 */
	public function getLength(): int {
		return count($this->path) - 1;
	}

	public function getNumWarps(): int {
		return $this->numWarps;
	}

	public function getTurns(): int {
		return $this->getLength() * TURNS_PER_SECTOR + $this->numWarps * (TURNS_PER_WARP - TURNS_PER_SECTOR);
	}

	public function getDistance(): int {
		return $this->getLength() + $this->numWarps * (TURNS_WARP_SECTOR_EQUIVALENCE - 1);
	}

	public function getEndSectorID(): int {
		return $this->path[count($this->path) - 1];
	}

	/**
	 * @return array<int, int>
	 */
	public function getPath(): array {
		return $this->path;
	}

	// NOTE: this assumes 2-way warps
	public function reversePath(): void {
		$this->path = array_reverse($this->path);
		$this->warpMap = array_flip($this->warpMap);
	}

	/**
	 * Add a neighboring sector to the path.
	 */
	public function addLink(int $nextSector): void {
		$this->path[] = $nextSector;
	}

	/**
	 * Add a warp to the path.
	 */
	public function addWarp(int $nextSector): void {
		$this->warpMap[$this->getEndSectorID()] = $nextSector;
		$this->numWarps++;
		$this->path[] = $nextSector;
	}

	public function getNextOnPath(): int {
		return $this->path[1];
	}

	public function getStartSectorID(): int {
		return $this->path[0];
	}

	public function followPath(): void {
		$nextSectorID = $this->getNextOnPath();
		if (in_array($nextSectorID, $this->warpMap)) {
			$this->numWarps--;
		}
		array_shift($this->path);
	}

	public function isInPath(int $sectorID): bool {
		return in_array($sectorID, $this->getPath());
	}

	/**
	 * If the given sector is in the path, then return the segment
	 * of the path that comes after the given sector.
	 */
	public function skipToSector(int $sectorID): self {
		$position = array_search($sectorID, $this->path);
		if ($position === false) {
			throw new Exception('Cannot skip to sector not in path!');
		}
		// The resulting path does include sectorID, i.e. [sectorID,end]
		$this->path = array_slice($this->path, $position);
		$this->numWarps = count(array_intersect($this->path, array_keys($this->warpMap)));
		return $this;
	}

}
