<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrSector;

class DragWarpProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		// Move a warp from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');

		$origSector = SmrSector::getSector($this->gameID, $origSectorID);
		$warpSector = $origSector->getWarpSector();
		$targetSector = SmrSector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a warp
		if (!$targetSector->hasWarp()) {
			$origSector->removeWarp();
			$targetSector->setWarp($warpSector);
			SmrSector::saveSectors();
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
