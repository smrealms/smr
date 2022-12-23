<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use SmrForce;

class ForcesRefreshProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $ownerAccountID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID);

		$forces->updateExpire();

		(new CurrentSector())->go();
	}

}
