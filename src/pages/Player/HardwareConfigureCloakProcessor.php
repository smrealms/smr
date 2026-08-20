<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class HardwareConfigureCloakProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly bool $disable,
	) {}

	public function build(Player $player): never {
		$ship = $player->getShip();

		if ($this->disable) {
			$ship->decloak();
		} else {
			if ($player->getTurns() < TURNS_TO_CLOAK) {
				create_error('You do not have enough turns to cloak.');
			}
			$player->takeTurns(TURNS_TO_CLOAK);
			$player->increaseHOF(TURNS_TO_CLOAK, ['Movement', 'Cloaking', 'Turns Used'], HOF_ALLIANCE);
			$player->increaseHOF(1, ['Movement', 'Cloaking', 'Times'], HOF_ALLIANCE);
			$ship->enableCloak();
		}

		new CurrentSector()->go();
	}

}
