<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class ManageDraftLeaders extends AccountPage {

	public string $file = 'admin/manage_draft_leaders.php';

	public function __construct(
		private readonly ?int $selectedGameID = null,
		private readonly ?string $processingMsg = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Manage Draft Leaders');

		$container = new ManageDraftLeadersSelectProcessor();
		$template->assign('SelectGameHREF', $container->href());

		// Get the list of active Draft games ordered by reverse start date
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE game_type = :game_type AND join_time < :now AND end_time > :now ORDER BY start_time DESC', [
			'game_type' => $db->escapeNumber(Game::GAME_TYPE_DRAFT),
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = [
				'game_name' => $dbRecord->getString('game_name'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}
		$template->assign('ActiveGames', $activeGames);

		if (count($activeGames) > 0) {
			// Set the selected game (or the first in the list if not selected yet)
			$selectedGameID = $this->selectedGameID ?? $activeGames[0]['game_id'];
			$template->assign('SelectedGame', $selectedGameID);

			// Get the list of current draft leaders for the selected game
			$currentLeaders = [];
			$dbResult = $db->read('SELECT account_id, home_sector_id FROM draft_leaders WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($selectedGameID),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$homeSectorID = $dbRecord->getInt('home_sector_id');
				$leader = Player::getPlayer($dbRecord->getInt('account_id'), $selectedGameID);
				$currentLeaders[] = [
					'Name' => $leader->getDisplayName(),
					'HomeSectorID' => $homeSectorID === 0 ? 'None' : $homeSectorID,
				];
			}
			$template->assign('CurrentLeaders', $currentLeaders);

			// If we have just forwarded from the processing file, pass its message.
			$template->assign('ProcessingMsg', $this->processingMsg);

			// Create the link to the processing file
			$linkContainer = new ManageDraftLeadersProcessor($selectedGameID);
			$template->assign('ProcessingHREF', $linkContainer->href());
		}
	}

}
