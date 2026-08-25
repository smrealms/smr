<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\ShipType;
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
		$dbResult = $db->select('npc_accounts');
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');

			$container = new NpcManageProcessor(
				selectedGameID: $selectedGameID,
				accountID: $accountID,
			);

			$npcs[$accountID] = [
				'default_player_name' => htmlentities($dbRecord->getString('default_player_name')),
				'default_alliance' => htmlentities($dbRecord->getString('default_alliance_name')),
				'href' => $container->href(),
				'player' => null,
			];
		}

		// Set the login name for the next NPC to create
		$nextNpcID = count($npcs) + 1;

		// Get a list of all possible ship types for creating NPCs
		// (Note: do this before getting individual NPC ship names as an optimization)
		$shipTypes = ShipType::getAll();

		// Get the existing NPC players for the selected game
		$dbResult = $db->select('npc_players', ['game_id' => $selectedGameID]);
		$npcPlayerSettings = [];
		foreach ($dbResult->records() as $dbRecord) {
			$npcPlayerSettings[$dbRecord->getInt('account_id')] = [
				'active' => $dbRecord->getBoolean('active'),
				'working' => $dbRecord->getBoolean('working'),
			];
		}

		$dbResult = $db->select(
			'player',
			['game_id' => $selectedGameID, 'npc' => $db->escapeBoolean(true)],
		);
		$npcPlayers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$npc = Player::getPlayer($accountID, $selectedGameID, false, $dbRecord);
			if (!array_key_exists($accountID, $npcs)) {
				throw new Exception('Found NPC not associated with account!');
			}
			$npcs[$accountID]['player'] = [
				'name' => $npc->getDisplayName(),
				'race' => $npc->getRaceName(),
				'alliance' => $npc->getAllianceDisplayName(),
				'ship' => ShipType::get($npc->getShipTypeID())->getName(),
				'disable_active_toggle' => (
					($npc->hasAlliance() && $npc->getAlliance()->isNpcForHire())
					|| $npc->isHiredNPC()
				),
				...$npcPlayerSettings[$accountID],
			];
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
			ShipTypes: $shipTypes,
		);
	}

}
