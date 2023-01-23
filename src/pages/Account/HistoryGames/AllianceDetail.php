<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Race;
use Smr\Template;

class AllianceDetail extends HistoryPage {

	public string $file = 'history_alliance_detail.php';

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName,
		private readonly int $allianceID,
		private readonly Summary|ExtendedStatsDetail $previousPage
	) {}

	protected function buildHistory(Account $account, Template $template): void {
		$this->addMenu($template, $this->previousPage::class);

		//offer a back button
		$template->assign('BackHREF', $this->previousPage->href());

		$game_id = $this->historyGameID;
		$id = $this->allianceID;

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT alliance_name, leader_id FROM alliance WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id));
		$dbRecord = $dbResult->record();
		$leaderID = $dbRecord->getInt('leader_id');
		$template->assign('PageTopic', 'Alliance Roster: ' . htmlentities($dbRecord->getString('alliance_name')));

		//get alliance members
		$oldAccountID = $account->getOldAccountID($this->historyDatabase);
		$dbResult = $db->read('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC');
		$players = [];
		foreach ($dbResult->records() as $dbRecord) {
			$memberAccountID = $dbRecord->getInt('account_id');
			$players[] = [
				'leader' => $memberAccountID == $leaderID ? '*' : '',
				'bold' => $memberAccountID == $oldAccountID ? 'class="bold"' : '',
				'player_name' => htmlentities($dbRecord->getString('player_name')),
				'experience' => $dbRecord->getInt('experience'),
				'alignment' => $dbRecord->getInt('alignment'),
				'race' => Race::getName($dbRecord->getInt('race')),
				'kills' => $dbRecord->getInt('kills'),
				'deaths' => $dbRecord->getInt('deaths'),
				'bounty' => $dbRecord->getInt('bounty'),
			];
		}
		$template->assign('Players', $players);
	}

}
