<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Force;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class ForcesRefreshProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $ownerAccountID,
	) {}

	public function build(Player $player): never {
		$forces = Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID);

		$forces->updateExpire();

		(new CurrentSector())->go();
	}

}
