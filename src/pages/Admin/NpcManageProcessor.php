<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\AllianceNotFound;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class NpcManageProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID,
		private readonly int $accountID,
		private readonly string $login,
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		// Change active status of an NPC
		if (Request::has('active-submit')) {
			// Toggle the activity of this NPC
			$active = Request::has('active');
			$db->update(
				'npc_logins',
				['active' => $db->escapeBoolean($active)],
				['login' => $this->login],
			);
		}

		// Create a new NPC player in a selected game
		if (Request::has('create_npc_player')) {
			$accountID = $this->accountID;
			$gameID = $this->selectedGameID;
			$playerName = Request::get('player_name');
			$raceID = Request::getInt('race_id');
			$npcPlayer = Player::createPlayer($accountID, $gameID, $playerName, $raceID, false, true);

			$npcPlayer->getShip()->setHardwareToMax();
			$npcPlayer->giveStartingTurns();
			$npcPlayer->setCredits(Game::getGame($gameID)->getStartingCredits());

			// Prevent them from triggering the newbie warning page
			$npcPlayer->setNewbieWarning(false);

			// Give a random alignment
			$npcPlayer->setAlignment(rand(-300, 300));

			$allianceName = Request::get('player_alliance');
			try {
				$alliance = Alliance::getAllianceByName($allianceName, $gameID);
			} catch (AllianceNotFound) {
				$alliance = Alliance::createAlliance($gameID, $allianceName);
				$alliance->setLeaderID($npcPlayer->getAccountID());
				$alliance->update();
				$alliance->createDefaultRoles();
			}
			$npcPlayer->joinAlliance($alliance->getAllianceID());

			// Update because we may not have a lock
			$npcPlayer->update();
			$npcPlayer->getShip()->update();
		}

		$container = new NpcManage($this->selectedGameID);
		$container->go();
	}

}
