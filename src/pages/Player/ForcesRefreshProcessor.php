<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Page\PlayerPageProcessor;

class ForcesRefreshProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $ownerAccountID
	) {}

	public function build(AbstractPlayer $player): never {
		$forces = Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID);

		$forces->updateExpire();

		(new CurrentSector())->go();
	}

}
