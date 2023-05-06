<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Template;

class HallOfFame extends HistoryPage {

	public string $file = 'history_games_hof.php';

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName,
		private readonly ?string $stat = null
	) {}

	protected function buildHistory(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Hall of Fame : ' . $this->historyGameName);
		$this->addMenu($template);

		$db = Database::getInstance();
		if ($this->stat === null) {
			// Display a list of stats available to view
			$links = [];
			$dbResult = $db->read('SHOW COLUMNS FROM player_has_stats');
			foreach ($dbResult->records() as $dbRecord) {
				$stat = $dbRecord->getString('Field');
				if ($stat === 'account_id' || $stat === 'game_id') {
					continue;
				}
				$statDisplay = ucwords(str_replace('_', ' ', $stat));
				$container = new self($this->historyDatabase, $this->historyGameID, $this->historyGameName, $stat);
				$links[] = create_link($container, $statDisplay);
			}
			$template->assign('Links', $links);
		} else {
			// Link back to overview page
			$container = new self($this->historyDatabase, $this->historyGameID, $this->historyGameName);
			$template->assign('BackHREF', $container->href());

			$statDisplay = ucwords(str_replace('_', ' ', $this->stat));
			$template->assign('StatName', $statDisplay);

			// Rankings display
			$oldAccountId = $account->getOldAccountID($this->historyDatabase);
			$dbResult = $db->read('SELECT * FROM player_has_stats JOIN player USING(account_id, game_id) WHERE game_id = :game_id ORDER BY player_has_stats.' . $this->stat . ' DESC LIMIT 25', [
				'game_id' => $db->escapeNumber($this->historyGameID),
			]);
			$rankings = [];
			foreach ($dbResult->records() as $dbRecord) {
				$rankings[] = [
					'bold' => $dbRecord->getInt('account_id') === $oldAccountId ? 'class="bold"' : '',
					'name' => $dbRecord->getString('player_name'),
					'stat' => $dbRecord->getInt($this->stat),
				];
			}
			$template->assign('Rankings', $rankings);
		}
	}

}
