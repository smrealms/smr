<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Exceptions\AllianceNotFound;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrAlliance;
use SmrGame;
use SmrPlayer;

class NpcManageProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID,
		private readonly int $accountID,
		private readonly string $login
	) {}

	public function build(SmrAccount $account): never {
		$db = Database::getInstance();

		// Change active status of an NPC
		if (Request::has('active-submit')) {
			// Toggle the activity of this NPC
			$active = Request::has('active');
			$db->write('UPDATE npc_logins SET active=' . $db->escapeBoolean($active) . ' WHERE login=' . $db->escapeString($this->login));
		}

		// Create a new NPC player in a selected game
		if (Request::has('create_npc_player')) {
			$accountID = $this->accountID;
			$gameID = $this->selectedGameID;
			$playerName = Request::get('player_name');
			$raceID = Request::getInt('race_id');
			$npcPlayer = SmrPlayer::createPlayer($accountID, $gameID, $playerName, $raceID, false, true);

			$npcPlayer->getShip()->setHardwareToMax();
			$npcPlayer->giveStartingTurns();
			$npcPlayer->setCredits(SmrGame::getGame($gameID)->getStartingCredits());

			// Prevent them from triggering the newbie warning page
			$npcPlayer->setNewbieWarning(false);

			// Give a random alignment
			$npcPlayer->setAlignment(rand(-300, 300));

			$allianceName = Request::get('player_alliance');
			try {
				$alliance = SmrAlliance::getAllianceByName($allianceName, $gameID);
			} catch (AllianceNotFound) {
				$alliance = SmrAlliance::createAlliance($gameID, $allianceName);
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