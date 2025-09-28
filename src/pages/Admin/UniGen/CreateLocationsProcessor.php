<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

function checkSectorAllowedForLoc(Sector $sector, Location $location): bool {
	if ($location->isHQ()) {
		// Only add HQs to empty sectors
		return !$sector->hasLocation();
	}
	// Otherwise, sector must meet these conditions:
	// 1. Does not already have this location
	// 2. Has fewer than 4 other locations
	// 3. Does not offer Fed protection
	return count($sector->getLocations()) < 4 && !$sector->offersFederalProtection() && !$sector->hasLocation($location->getTypeID());
}

class CreateLocationsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$galSectors = Sector::getGalaxySectors($this->gameID, $this->galaxyID);
		foreach ($galSectors as $galSector) {
			$galSector->removeAllLocations();
		}
		foreach (Location::getAllLocations($this->gameID) as $location) {
			if (Request::has('loc' . $location->getTypeID())) {
				$numLoc = Request::getInt('loc' . $location->getTypeID());
				for ($i = 0; $i < $numLoc; $i++) {
					//4 per sector max locs and no locations inside fed
					$randSector = findValidSector(
						$galSectors,
						fn(Sector $sector): bool => checkSectorAllowedForLoc($sector, $location),
					);
					$randSector->addLocation($location);
					$randSector->addLinkedLocations($location);
				}
			}
		}

		$this->returnTo->message = '<span class="green">Success</span> : added locations.';
		$this->returnTo->go();
	}

}
