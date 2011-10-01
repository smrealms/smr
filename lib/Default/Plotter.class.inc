<?php
require_once(get_file_loc('SmrSector.class.inc'));
class Plotter
{
	public function __construct()
	{
	}

	public static function &findDistanceToX($x, SmrSector &$sector, $useFirst, AbstractSmrPlayer $needsToHaveBeenExploredBy=null, AbstractSmrPlayer $player=null, $distanceLimit=10000, $lowLimit=0, $highLimit=100000)
	{
		$checkSector =& $sector;
		$gameID = $sector->getGameID();
		$distances = array();
		$sectorsTravelled = 0;
		$visitedSectors = array();
		$visitedSectors[$checkSector->getSectorID()] = true;

		$distanceQ = array();
		for($i=0;$i<=TURNS_WARP_SECTOR_EQUIVALENCE;$i++)
			$distanceQ[] = array();
		//Warps first as a slight optimisation due to how visitedSectors is set.
		if($checkSector->hasWarp()===true)
		{
			$d = new Distance($gameID,$checkSector->getSectorID());
			$d->addWarpToPath($checkSector->getWarp());
			$distanceQ[TURNS_WARP_SECTOR_EQUIVALENCE][] = $d;
		}
		foreach ($checkSector->getLinks() as $nextSector)
		{
			if($nextSector!==0)
			{
				$visitedSectors[$nextSector] = true;
				$d = new Distance($gameID,$checkSector->getSectorID());
				$d->addToPath($nextSector);
				$distanceQ[0][] = $d;
			}
		}
		$maybeWarps=0;
		while ($maybeWarps<=TURNS_WARP_SECTOR_EQUIVALENCE)
		{
			$sectorsTravelled++;
			if ($sectorsTravelled > $distanceLimit)
				return $distances;
			$distanceQ[] = array();
			if(count($q = array_shift($distanceQ))===0)
			{
				$maybeWarps++;
				continue;
			}
			$maybeWarps=0;
			while (($distance = array_shift($q))!==null)
			{
				$checkSectorID = $distance->getEndSectorID();
				$visitedSectors[$checkSectorID] = true; // This is here for warps, because they are delayed visits if we set this before the actual visit we'll get sectors marked as visited long before they are actually visited - causes problems when it's quicker to walk to the warp exit than to warp there.
																// We still need to mark walked sectors as visited before we go to each one otherwise we get a huge number of paths being checked twice (up then left, left then up are essentially the same but if we set up-left as visited only when we actually check it then it gets queued up twice - nasty)
				if ($checkSectorID >= $lowLimit && $checkSectorID <= $highLimit)
				{
					$checkSector =& SmrSector::getSector($gameID,$checkSectorID);
					if (($x=='Distance' && $sectorsTravelled==$distanceLimit)
						|| (($needsToHaveBeenExploredBy===null||$needsToHaveBeenExploredBy->hasVisitedSector($checkSector->getSectorID()))===true
							&& $checkSector->hasX($x,$player)===true))
					{
						if($useFirst===true)
							return $distance;
						$distances[$checkSector->getSectorID()] = $distance;
					}
					//Warps first as a slight optimisation due to how visitedSectors is set.
					if($checkSector->hasWarp()===true)
					{
						if (!isset($visitedSectors[$checkSector->getWarp()]))
						{
							$cloneDistance = clone($distance);
							$cloneDistance->addWarpToPath($checkSector->getWarp());
							$distanceQ[TURNS_WARP_SECTOR_EQUIVALENCE][] = $cloneDistance;
						}
					}
					foreach ($checkSector->getLinks() as $nextSector)
					{
						if (!isset($visitedSectors[$nextSector]))
						{
							$visitedSectors[$nextSector] = true;
	
							$cloneDistance = clone($distance);
							$cloneDistance->addToPath($nextSector);
							$distanceQ[0][] = $cloneDistance;
						}
					}
				}
			}
		}
		if($useFirst===true)
		{
			$return = false;
			return $return;
		}
		return $distances;
	}
	
	public static function &calculatePortToPortDistances(array &$sectors, $distanceLimit = 10000, $lowLimit = 0, $highLimit = 100000)
	{
		$distances = array();
		foreach($sectors as &$sec)
		{
			if ($sec !== null)
			{
				if ($sec->getSectorID() >= $lowLimit && $sec->getSectorID() <= $highLimit)
				{
					if ($sec->hasPort()===true)
					{
						$distances[$sec->getSectorID()] =& self::findDistanceToOtherPorts($sec, $distanceLimit, $lowLimit, $highLimit);
					}
				}
			}
		} unset($sec);
		return $distances;
	}

	public static function &findDistanceToOtherPorts(SmrSector &$sector, $distanceLimit = 10000, $lowLimit = 0, $highLimit = 100000)
	{
		return self::findDistanceToX('Port', $sector, false, null, null, $distanceLimit, $lowLimit, $highLimit, $distanceLimit);
	}
}

class Distance
{
	private $gameID;
	private $distance = -1; //First sector added will be the start and a distance of 0
	private $numWarps = 0;
	private $path = array();

	public function __construct($gameID,$_startSectorId)
	{
		$this->gameID = $gameID;
		$this->addToPath($_startSectorId);
	}

//	public function __construct($_distance, $_numWarps, array $_path)
//	{
//		$this->distance = $_distance;
//		$this->numWarps = $_numWarps;
//		$this->path = $_path;
//	}

	public function setDistance($_distance)
	{
		$this->distance = $_distance;
	}

	public function setNumWarps($_numWarps)
	{
		$this->numWarps = $_numWarps;
	}

	protected function incrementDistance()
	{
		$this->distance++;
	}

	protected function incrementNumWarps()
	{
		$this->numWarps++;
	}

	public function getDistance()
	{
		return $this->distance;
	}

	public function getTotalSectors()
	{
		return $this->getDistance()+$this->getNumWarps();
	}

	public function getNumWarps()
	{
		return $this->numWarps;
	}

//	public function clone()
//	{
//		return new Distance($this->distance, $this->numWarps, clone($this->path));
//	}

	public function getTurns()
	{
		return $this->distance * TURNS_PER_SECTOR + $this->numWarps * TURNS_PER_WARP;
	}

	public function getRelativeDistance()
	{
		return $this->distance + $this->numWarps * TURNS_WARP_SECTOR_EQUIVALENCE;
	}

	/**
	 * @return the endSectorId
	 */
	public function getEndSectorID()
	{
		return $this->path[count($this->path)-1];
	}

	public function &getEndSector()
	{
		return SmrSector::getSector($this->gameID,$this->getEndSectorID());
	}

	/**
	 * @return the startSectorId
	 */
	public function getStartSectorId()
	{
		return $this->path[0];
	}

	public function compareTo(Distance &$d)
	{
		if ($this->equals($d)===true)
			return 0;
		else if ($d->getTurns() > $this->getTurns())
			return -1;
		return 1;
	}

	/**
	 * @return the path
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	public function reversePath()
	{
		$this->path = array_reverse($this->path);
	}

	/**
	 * @param path
	 *            the path to set
	 */
	public function addToPath($nextSector)
	{
		$this->incrementDistance();
		$this->path[] = $nextSector;
	}
	
	public function addWarpToPath($nextSector)
	{
		$this->incrementNumWarps();
		$this->path[] = $nextSector;
	}
	
	public function getNextOnPath()
	{
		return $this->path[0];
	}
	
	public function followPath($isWarp=false)
	{
		if($isWarp===false)
			$this->distance--;
		else
			$this->numWarps--;
		return array_shift($this->path);
	}
	
	public function removeStart()
	{
		return array_shift($this->path);
	}
	
	public function isInPath($sectorID)
	{
		return in_array($sectorID,$this->getPath());
	}
}
//$d = new Distance(1);
//$d->addToPath(3);
//$d2 = clone($d);
//$d2->addWarpToPath(6);
//var_dump($d);
//var_dump($d2);

//$x = 1;

//$x = Globals::getGood(1);
//$x['TransactionType'] = 'Sell';

//$x =& SmrSector::getSector(1,2);
//var_dump($x);
//var_dump(Plotter::findDistanceToX($x, SmrSector::getSector(1,1), 0, 2000, 500,true));
?>