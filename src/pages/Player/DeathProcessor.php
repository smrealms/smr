<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class DeathProcessor extends PlayerPageProcessor {

	public function __construct() {
		$this->skipRedirect = true;
	}

	public function build(AbstractSmrPlayer $player): never {
		$player->setDead(false);

		$player->log(LOG_TYPE_TRADER_COMBAT, 'Player sees death screen');
		(new Death())->go();
	}

}
