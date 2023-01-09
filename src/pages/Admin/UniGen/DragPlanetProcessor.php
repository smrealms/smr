<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Planet;
use Smr\Request;
use Smr\Sector;

class DragPlanetProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(Account $account): never {
		// Move a planet from one sector to another (note that this will
		// currently only retain the planet type and inhabitable time).
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$origPlanet = Planet::getPlanet($this->gameID, $origSectorID);
		$targetSector = Sector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a planet
		if (!$targetSector->hasPlanet()) {
			// Create first so that if there is an error the planet doesn't disappear
			Planet::createPlanet($this->gameID, $targetSectorID, $origPlanet->getTypeID(), $origPlanet->getInhabitableTime());
			Planet::removePlanet($this->gameID, $origSectorID);
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
