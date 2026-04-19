<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class NewbieLeaveProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$action = Request::get('action');
		if ($action === 'Yes!') {
			$player->setNewbieTurns(0);
			$player->setNewbieWarning(false);
		}

		$player->log(LOG_TYPE_MOVEMENT, 'Player drops newbie turns.');
		(new CurrentSector())->go();
	}

}
