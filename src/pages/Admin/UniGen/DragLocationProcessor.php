<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrLocation;
use SmrSector;

class DragLocationProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		// Move a location from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$locationTypeID = Request::getInt('LocationTypeID');
		$targetSector = SmrSector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has the same location
		if (!$targetSector->hasLocation($locationTypeID)) {
			$location = SmrLocation::getLocation($this->gameID, $locationTypeID);
			SmrLocation::moveSectorLocation($this->gameID, $origSectorID, $targetSectorID, $location);
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
