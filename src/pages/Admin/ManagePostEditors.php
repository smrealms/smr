<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Globals;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;
use SmrPlayer;

class ManagePostEditors extends AccountPage {

	public string $file = 'admin/manage_post_editors.php';

	public function __construct(
		private readonly ?int $selectedGameID = null,
		private readonly ?string $processingMsg = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Manage Galactic Post Editors');

		$container = new ManagePostEditorsSelectProcessor();
		$template->assign('SelectGameHREF', $container->href());

		// Get the list of active games ordered by reverse start date
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE join_time < ' . $db->escapeNumber(Epoch::time()) . ' AND end_time > ' . $db->escapeNumber(Epoch::time()) . ' ORDER BY start_time DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = [
				'game_name' => $dbRecord->getString('game_name'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}
		$template->assign('ActiveGames', $activeGames);

		if ($activeGames) {
			// Set the selected game (or the first in the list if not selected yet)
			$selectedGameID = $this->selectedGameID ?? $activeGames[0]['game_id'];
			$template->assign('SelectedGame', $selectedGameID);

			// Get the list of current editors for the selected game
			$currentEditors = [];
			foreach (Globals::getGalacticPostEditorIDs($selectedGameID) as $editorID) {
				$editor = SmrPlayer::getPlayer($editorID, $selectedGameID);
				$currentEditors[] = $editor->getDisplayName();
			}
			$template->assign('CurrentEditors', $currentEditors);

			// If we have just forwarded from the processing file, pass its message.
			$template->assign('ProcessingMsg', $this->processingMsg);

			// Create the link to the processing file
			$linkContainer = new ManagePostEditorsProcessor($selectedGameID);
			$template->assign('PostEditorHREF', $linkContainer->href());
		}
	}

}
