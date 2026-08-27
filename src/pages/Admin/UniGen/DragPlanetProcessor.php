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
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		// Move a planet from one sector to another.
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$targetSector = Sector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a planet
		if (!$targetSector->hasPlanet()) {
			Planet::movePlanet($this->gameID, $origSectorID, $targetSectorID);
		}

		$this->returnTo->go();
	}

}
