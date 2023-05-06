<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class HardwareConfigureProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly string $action
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();

		if ($this->action === 'Enable Cloak') {
			if ($player->getTurns() < TURNS_TO_CLOAK) {
				create_error('You do not have enough turns to cloak.');
			}
			$player->takeTurns(TURNS_TO_CLOAK);
			$player->increaseHOF(TURNS_TO_CLOAK, ['Movement', 'Cloaking', 'Turns Used'], HOF_ALLIANCE);
			$player->increaseHOF(1, ['Movement', 'Cloaking', 'Times'], HOF_ALLIANCE);
			$ship->enableCloak();
		} elseif ($this->action === 'Disable Cloak') {
			$ship->decloak();
		} elseif ($this->action === 'Set Illusion') {
			$ship->setIllusion(Request::getInt('ship_type_id'), Request::getInt('attack'), Request::getInt('defense'));
		} elseif ($this->action === 'Disable Illusion') {
			$ship->disableIllusion();
		}

		$container = new CurrentSector();
		$container->go();
	}

}
