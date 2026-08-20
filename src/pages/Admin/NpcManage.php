<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class NpcManage extends AccountPage {

	public function __construct(
		private readonly ?int $selectedGameID = null,
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Manage NPCs';

		$selectedGameID = $this->selectedGameID;

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

		// Get the existing NPC players for the selected game
		$dbResult = $db->select('player', [
			'game_id' => $selectedGameID,
			'npc' => $db->escapeBoolean(true),
		]);
		$npcPlayers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$npc = Player::getPlayer($accountID, $selectedGameID, false, $dbRecord);
			if (!array_key_exists($accountID, $npcs)) {
				throw new Exception('Found NPC not associated with login!');
			}
			$npcs[$accountID]['player'] = $npc;
			if (($npc->hasAlliance() && $npc->getAlliance()->isNpcForHire()) || $npc->isHiredNPC()) {
				$npcs[$accountID]['disable_active_toggle'] = true;
			}
			$npcPlayers[] = $npc;
		}

		// Get galaxy/alliance options for NPC galaxies
		$npcGalaxyChoices = [];
		if (Game::gameExists($selectedGameID)) {
			foreach (Game::getGame($selectedGameID)->getGalaxies() as $galaxy) {
				if ($galaxy->getGalaxyType() !== Galaxy::TYPE_RACIAL) {
					$npcGalaxyChoices[] = $galaxy;
				}
			}
		}
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

		$template->pageRenderer = fn() => NpcManageRenderer::render(
			SelectGameHREF: new NpcManageSelectProcessor()->href(),
			Message: $this->message,
			Games: $games,
			SelectedGameID: $selectedGameID,
			AddAccountHREF: new NpcManageAddAccountProcessor($selectedGameID)->href(),
			NextLogin: 'npc' . $nextNpcID,
			Npcs: $npcs,
			NpcGalaxyChoices: $npcGalaxyChoices,
			NpcGalaxyAllianceChoices: $npcGalaxyAllianceChoices,
			SetupNpcGalaxyHref: new NpcManageSetupGalaxyProcessor($selectedGameID)->href(),
		);
	}

}
