<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Game;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class AllianceRemoveMemberProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$accountIDs = Request::getIntArray('account_id', []);

		if (count($accountIDs) === 0) {
			create_error('You have to choose someone to remove them!');
		}

		if (in_array($player->getAlliance()->getLeaderID(), $accountIDs, true)) {
			create_error('You can\'t kick the leader!');
		}

		if (in_array($player->getAccountID(), $accountIDs, true)) {
			create_error('You can\'t kick yourself!');
		}

		foreach ($accountIDs as $accountID) {
			$currPlayer = Player::getPlayer($accountID, $player->getGameID());
			if (!$player->sameAlliance($currPlayer)) {
				throw new Exception('Cannot kick someone from another alliance!');
			}
			$currPlayer->leaveAlliance($player);

			// In Draft games, banish the player to sector 1
			if ($player->getGame()->isGameType(Game::GAME_TYPE_DRAFT)) {
				$currPlayer->setSectorID(1);
				$currPlayer->setNewbieTurns(max(1, $currPlayer->getNewbieTurns()));
				$currPlayer->setLandedOnPlanet(false);
			}

			$currPlayer->update(); // we need better locking here
		}

		(new AllianceRoster())->go();
	}

}
