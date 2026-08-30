<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class AllianceSetFlagshipProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$alliance = $player->getAlliance();

		$flagshipPlayerID = Request::getInt('flagship_player_id');
		if ($flagshipPlayerID !== 0) {
			$flagshipPlayer = Player::getPlayer($flagshipPlayerID);
			if (!$player->sameAlliance($flagshipPlayer)) {
				throw new Exception('Cannot make a player from another alliance the flagship.');
			}
		}

		$alliance->setFlagshipPlayerID($flagshipPlayerID);
		$alliance->update();

		new AllianceSetOp()->go();
	}

}
