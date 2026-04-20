<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class NpcManage extends AccountPage {

	public string $file = 'admin/npc_manage.php';

	public function __construct(
		private readonly ?int $selectedGameID = null,
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Manage NPCs');

		$selectedGameID = $this->selectedGameID;

		$container = new NpcManageSelectProcessor();
		$template->assign('SelectGameHREF', $container->href());

		$template->assign('Message', $this->message);

		$games = [];
		foreach (Game::getActiveGames() as $gameID => $game) {
			if ($selectedGameID === null) {
				$selectedGameID = $gameID;
			}
			$games[] = [
				'Name' => $game->getDisplayName(),
				'ID' => $gameID,
				'Selected' => $gameID === $selectedGameID,
			];
		}
		$selectedGameID ??= 0; // no valid games found

		$template->assign('Games', $games);
		$template->assign('SelectedGameID', $selectedGameID);

		$container = new NpcManageAddAccountProcessor($selectedGameID);
		$template->assign('AddAccountHREF', $container->href());

		$npcs = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM npc_logins JOIN account USING(login)');
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$login = $dbRecord->getString('login');

			$container = new NpcManageProcessor(
				selectedGameID: $selectedGameID,
				login: $login,
				accountID: $accountID,
			);

			$npcs[$accountID] = [
				'login' => $login,
				'default_player_name' => htmlentities($dbRecord->getString('player_name')),
				'default_alliance' => htmlentities($dbRecord->getString('alliance_name')),
				'active' => $dbRecord->getBoolean('active'),
				'working' => $dbRecord->getBoolean('working'),
				'href' => $container->href(),
				'disable_active_toggle' => false,
			];
		}

		// Set the login name for the next NPC to create
		$nextNpcID = count($npcs) + 1;
		$template->assign('NextLogin', 'npc' . $nextNpcID);

		// Get the existing NPC players for the selected game
		$dbResult = $db->select('player', [
			'game_id' => $selectedGameID,
			'npc' => $db->escapeBoolean(true),
		]);
		$npcPlayers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$npc = Player::getPlayer($accountID, $selectedGameID, false, $dbRecord);
			$npcs[$accountID]['player'] = $npc;
			if (($npc->hasAlliance() && $npc->getAlliance()->isNpcForHire()) || $npc->isHiredNPC()) {
				$npcs[$accountID]['disable_active_toggle'] = true;
			}
			$npcPlayers[] = $npc;
		}

		$template->assign('Npcs', $npcs);

		// Get galaxy/alliance options for NPC galaxies
		$npcGalaxyChoices = [];
		foreach (Game::getGame($selectedGameID)->getGalaxies() as $galaxy) {
			if ($galaxy->getGalaxyType() !== Galaxy::TYPE_RACIAL) {
				$npcGalaxyChoices[] = $galaxy;
			}
		}
		$template->assign('NpcGalaxyChoices', $npcGalaxyChoices);
		$npcGalaxyAllianceChoices = [];
		foreach ($npcPlayers as $npc) {
			if (!$npc->hasAlliance()) {
				continue;
			}
			$alliance = $npc->getAlliance();
			if (!$alliance->isNpcForHire() && $alliance->hasLeader() && $alliance->getLeader()->isNPC()) {
				$npcGalaxyAllianceChoices[$alliance->getAllianceID()] = $alliance->getAllianceDisplayName();
			}
		}
		$template->assign('NpcGalaxyAllianceChoices', $npcGalaxyAllianceChoices);
		$template->assign('SetupNpcGalaxyHref', (new NpcManageSetupGalaxyProcessor($selectedGameID))->href());
	}

}
