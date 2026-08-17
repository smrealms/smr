<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Pages\Shared\PreviousGameAllianceListRenderer;
use Smr\Race;
use Smr\Template;

class AllianceDetail extends HistoryPage {

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName,
		private readonly int $allianceID,
		private readonly Summary|ExtendedStatsDetail $previousPage,
	) {}

	protected function buildHistory(Account $account, Template $template): void {
		$this->addMenu($template, $this->previousPage::class);

		//offer a back button

		$game_id = $this->historyGameID;
		$id = $this->allianceID;

		$db = Database::getInstance();
		$dbResult = $db->select(
			'alliance',
			['alliance_id' => $id, 'game_id' => $game_id],
			['alliance_name', 'leader_id'],
		);
		$dbRecord = $dbResult->record();
		$leaderID = $dbRecord->getInt('leader_id');
		$template->pageTopic = 'Alliance Roster: ' . htmlentities($dbRecord->getString('alliance_name'));

		//get alliance members
		$oldAccountID = $account->getOldAccountID($this->historyDatabase);
		$dbResult = $db->select(
			'player',
			[
				'alliance_id' => $id,
				'game_id' => $game_id,
			],
			orderBy: ['experience'],
			order: ['DESC'],
		);
		$players = [];
		foreach ($dbResult->records() as $dbRecord) {
			$memberAccountID = $dbRecord->getInt('account_id');
			$players[] = [
				'leader' => $memberAccountID === $leaderID ? '*' : '',
				'bold' => $memberAccountID === $oldAccountID ? 'class="bold"' : '',
				'player_name' => htmlentities($dbRecord->getString('player_name')),
				'experience' => $dbRecord->getInt('experience'),
				'alignment' => $dbRecord->getInt('alignment'),
				'race' => Race::getName($dbRecord->getInt('race')),
				'kills' => $dbRecord->getInt('kills'),
				'deaths' => $dbRecord->getInt('deaths'),
				'bounty' => $dbRecord->getInt('bounty'),
			];
		}

		$template->pageRenderer = fn() => PreviousGameAllianceListRenderer::render(
			BackHREF: $this->previousPage->href(),
			Players: $players,
		);
	}

}
