<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class BondProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}
		$planet = $player->getSectorPlanet();

		// Player has confirmed the request to bond
		$planet->bond();

		$player->log(LOG_TYPE_BANK, 'Player bonds ' . $planet->getBonds() . ' credits at planet.');

		new Financial()->go();
	}

}
