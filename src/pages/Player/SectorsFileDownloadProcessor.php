<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\SectorLock;
use Smr\SectorsFile;

class SectorsFileDownloadProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function build(AbstractPlayer $player): never {
		// We can release the sector lock now because we know that the following
		// code is read-only. This will help reduce sector lag and possible abuse.
		SectorLock::getInstance()->release();

		SectorsFile::create($player->getGameID(), $player);
	}

}
