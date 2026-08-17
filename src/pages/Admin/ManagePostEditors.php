<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class ManagePostEditors extends AccountPage {

	public function __construct(
		private readonly ?int $selectedGameID = null,
		private readonly ?string $processingMsg = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Manage Galactic Post Editors';

		// Get the list of active games ordered by reverse start date
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE join_time < :now AND end_time > :now ORDER BY start_time DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = [
				'game_name' => $dbRecord->getString('game_name'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}

		if (count($activeGames) > 0) {
			// Set the selected game (or the first in the list if not selected yet)
			$selectedGameID = $this->selectedGameID ?? $activeGames[0]['game_id'];

			// Get the list of current editors for the selected game
			$currentEditors = [];
			foreach (Globals::getGalacticPostEditorIDs($selectedGameID) as $editorID) {
				$editor = Player::getPlayer($editorID, $selectedGameID);
				$currentEditors[] = $editor->getDisplayName();
			}

			$template->pageRenderer = fn() => ManagePostEditorsRenderer::render(
				SelectGameHREF: new ManagePostEditorsSelectProcessor()->href(),
				ActiveGames: $activeGames,
				SelectedGame: $selectedGameID,
				CurrentEditors: $currentEditors,
				ProcessingMsg: $this->processingMsg,
				PostEditorPage: new ManagePostEditorsProcessor($selectedGameID),
			);
		} else {
			$template->pageRenderer = fn() => ManagePostEditorsRenderer::renderEmpty();
		}
	}

}
