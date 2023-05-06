<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class NpcManage extends AccountPage {

	public string $file = 'admin/npc_manage.php';

	public function __construct(
		private readonly ?int $selectedGameID = null
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Manage NPCs');

		$selectedGameID = $this->selectedGameID;

		$container = new NpcManageSelectProcessor();
		$template->assign('SelectGameHREF', $container->href());

		$games = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > :now AND enabled = :enabled ORDER BY game_id DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
			'enabled' => $db->escapeBoolean(true),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$gameID = $dbRecord->getInt('game_id');
			if (empty($selectedGameID)) {
				$selectedGameID = $gameID;
			}
			$games[] = [
				'Name' => Game::getGame($gameID)->getDisplayName(),
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
			];
		}

		// Set the login name for the next NPC to create
		$nextNpcID = count($npcs) + 1;
		$template->assign('NextLogin', 'npc' . $nextNpcID);

		// Get the existing NPC players for the selected game
		$dbResult = $db->read('SELECT * FROM player WHERE game_id = :game_id AND npc = :npc', [
			'game_id' => $db->escapeNumber($selectedGameID),
			'npc' => $db->escapeBoolean(true),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$npcs[$accountID]['player'] = Player::getPlayer($accountID, $selectedGameID, false, $dbRecord);
		}

		$template->assign('Npcs', $npcs);
	}

}
