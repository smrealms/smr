<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrSector;

class ToggleLinkProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		$linkSector = SmrSector::getSector($this->gameID, Request::getInt('SectorID'));
		$linkSector->toggleLink(Request::get('Dir'));
		SmrSector::saveSectors();

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$container->go();
	}

}
