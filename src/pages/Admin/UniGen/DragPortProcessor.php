<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrPort;
use SmrSector;

class DragPortProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		// Move a port from one sector to another
		$targetSectorID = Request::getInt('TargetSectorID');
		$origSectorID = Request::getInt('OrigSectorID');
		$targetSector = SmrSector::getSector($this->gameID, $targetSectorID);

		// Skip if target sector already has a port
		if (!$targetSector->hasPort()) {
			$oldPort = SmrPort::getPort($this->gameID, $origSectorID);

			$newPort = SmrPort::createPort($this->gameID, $targetSectorID);
			$newPort->setRaceID($oldPort->getRaceID());
			$newPort->setLevel($oldPort->getLevel());
			$newPort->setPortGoods($oldPort->getGoodTransactions());
			$newPort->setCreditsToDefault();
			$newPort->update();

			SmrPort::removePort($this->gameID, $origSectorID);
		}

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
