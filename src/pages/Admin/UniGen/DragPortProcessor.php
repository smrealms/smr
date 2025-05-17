<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Port;
use Smr\Request;
use Smr\Sector;

class DragPortProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		// Move a port from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$targetSector = Sector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a port
		if (!$targetSector->hasPort()) {
			$oldPort = Port::getPort($this->gameID, $origSectorID);

			$newPort = Port::createPort($this->gameID, $targetSectorID);
			$newPort->setRaceID($oldPort->getRaceID());
			$newPort->setLevel($oldPort->getLevel());
			$newPort->setPortGoods($oldPort->getGoodTransactions());
			$newPort->setCreditsToDefault();
			$newPort->update();

			Port::removePort($this->gameID, $origSectorID);
		}

		$this->returnTo->go();
	}

}
