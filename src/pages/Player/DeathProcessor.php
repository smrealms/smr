<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class DeathProcessor extends PlayerPageProcessor {

	public function __construct() {
		$this->skipRedirect = true;
	}

	public function build(Player $player): never {
		$player->setDead(false);

		$player->log(LOG_TYPE_TRADER_COMBAT, 'Player sees death screen');
		(new Death())->go();
	}

}
