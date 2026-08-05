<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\Port;
use Smr\Race;
use Smr\Request;
use Smr\Sector;

class CreatePortsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$numLevelPorts = [];
		$maxPortLevel = Port::getMaxLevelByGame($this->gameID);
		for ($i = 1; $i <= $maxPortLevel; $i++) {
			$numLevelPorts[$i] = Request::getInt('port' . $i);
		}
		$totalPorts = array_sum($numLevelPorts);

		$totalRaceDist = 0;
		$numRacePorts = [];
		foreach (Race::getAllIDs() as $raceID) {
			$racePercent = Request::getInt('race' . $raceID);
			if ($racePercent !== 0) {
				$totalRaceDist += $racePercent;
				$numRacePorts[$raceID] = ICeil($racePercent / 100 * $totalPorts);
			}
		}
		if ($totalRaceDist === 100 || $totalPorts === 0) {
			$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
			self::createPorts($galaxy, $numRacePorts, $numLevelPorts);
			$message = '<span class="green">Success</span> : added ports.';
		} else {
			$message = '<span class="red">Error: Your port race distribution must equal 100!</span>';
		}

		$this->returnTo->message = $message;
		$this->returnTo->go();
	}

	/**
	 * @param array<int, int> $numRacePorts Number of ports for each race
	 * @param array<int, int> $numLevelPorts Number of ports at each level
	 */
	public static function createPorts(
		Galaxy $galaxy,
		array $numRacePorts,
		array $numLevelPorts,
		bool $removeExisting = true,
	): void {
		$totalPorts = array_sum($numLevelPorts);
		$assignedPorts = array_sum($numRacePorts);

		$galSectors = $galaxy->getSectors();
		foreach ($galSectors as $galSector) {
			if ($removeExisting && $galSector->hasPort()) {
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
					fn(Sector $sector): bool => !$sector->hasPort() && !$sector->offersFederalProtection(),
				);

				$raceID = array_rand($numRacePorts);
				$numRacePorts[$raceID]--;
				if ($numRacePorts[$raceID] === 0) {
					unset($numRacePorts[$raceID]);
				}
				$port = $galSector->createPort();
				$port->setRaceID($raceID);
				$port->upgradeToLevel($portLevel);
				$port->setCreditsToDefault();
			}
		}

		Port::savePorts();
	}

}
