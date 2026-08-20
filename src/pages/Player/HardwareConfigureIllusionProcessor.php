<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class HardwareConfigureIllusionProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly bool $disable,
	) {}

	public function build(Player $player): never {
		$ship = $player->getShip();

		if ($this->disable) {
			$ship->disableIllusion();
		} else {
			$ship->setIllusion(Request::getInt('ship_type_id'), Request::getInt('attack'), Request::getInt('defense'));
		}

		new CurrentSector()->go();
	}

}
