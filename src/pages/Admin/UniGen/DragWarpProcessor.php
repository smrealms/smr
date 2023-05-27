<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class DragWarpProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
	) {}

	public function build(Account $account): never {
		// Move a warp from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');

		$origSector = Sector::getSector($this->gameID, $origSectorID);
		$warpSector = $origSector->getWarpSector();
		$targetSector = Sector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a warp
		if (!$targetSector->hasWarp()) {
			$origSector->removeWarp();
			$targetSector->setWarp($warpSector);
			Sector::saveSectors();
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
