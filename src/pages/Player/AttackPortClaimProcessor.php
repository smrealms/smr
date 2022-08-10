<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class AttackPortClaimProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$port = $player->getSectorPort();
		$port->setRaceID($player->getRaceID());

		$port->getLootHREF(true)->go();
	}

}
