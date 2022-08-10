<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrPlanet;
use SmrSector;

class DragPlanetProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		// Move a planet from one sector to another (note that this will
		// currently only retain the planet type and inhabitable time).
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$origPlanet = SmrPlanet::getPlanet($this->gameID, $origSectorID);
		$targetSector = SmrSector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a planet
		if (!$targetSector->hasPlanet()) {
			// Create first so that if there is an error the planet doesn't disappear
			SmrPlanet::createPlanet($this->gameID, $targetSectorID, $origPlanet->getTypeID(), $origPlanet->getInhabitableTime());
			SmrPlanet::removePlanet($this->gameID, $origSectorID);
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
