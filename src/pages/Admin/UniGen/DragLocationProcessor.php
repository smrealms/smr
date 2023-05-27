<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class DragLocationProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
	) {}

	public function build(Account $account): never {
		// Move a location from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$locationTypeID = Request::getInt('LocationTypeID');
		$targetSector = Sector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has the same location
		if (!$targetSector->hasLocation($locationTypeID)) {
			$location = Location::getLocation($this->gameID, $locationTypeID);
			Location::moveSectorLocation($this->gameID, $origSectorID, $targetSectorID, $location);
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
