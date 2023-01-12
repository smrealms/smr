<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

class AttackPortClaimProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$port = $player->getSectorPort();
		$port->setRaceID($player->getRaceID());

		$port->getLootHREF(true)->go();
	}

}
