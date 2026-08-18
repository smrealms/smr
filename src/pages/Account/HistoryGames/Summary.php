<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Template;

class Summary extends HistoryPage {

	protected function buildHistory(Account $account, Template $template): void {
		//topic
		$game_name = $this->historyGameName;
		$game_id = $this->historyGameID;
		$template->pageTopic = 'Old SMR Game : ' . $game_name;
		$this->addMenu($template);

		$db = Database::getInstance();
		$dbResult = $db->select('game', ['game_id' => $game_id]);
		$dbRecord = $dbResult->record();
		$startDate = date($account->getDateFormat(), $dbRecord->getInt('start_date'));
		$endDate = date($account->getDateFormat(), $dbRecord->getInt('end_date'));
		$gameType = $dbRecord->getString('type');
		$gameSpeed = $dbRecord->getFloat('speed');

		$dbResult = $db->read('SELECT count(*) total_players, IFNULL(max(experience),0) max_exp, IFNULL(max(alignment),0) max_align, IFNULL(min(alignment),0) min_align, IFNULL(max(kills),0) max_kills FROM player WHERE game_id = :game_id', [
			'game_id' => $db->escapeNumber($game_id),
		]);
		$dbRecord = $dbResult->record();
		$numPlayers = $dbRecord->getInt('total_players');
		$maxExp = $dbRecord->getInt('max_exp');
		$maxAlign = $dbRecord->getInt('max_align');
		$minAlign = $dbRecord->getInt('min_align');

		// Get linked player information, if available
		$oldAccountID = $account->getOldAccountID($this->historyDatabase);
		$dbResult = $db->select(
			'player',
			['game_id' => $game_id, 'account_id' => $oldAccountID],
			['alliance_id'],
		);
		$oldAllianceID = $dbResult->hasRecord() ? $dbResult->record()->getInt('alliance_id') : 0;

		$playerExp = [];
		$dbResult = $db->select(
			'player',
			['game_id' => $game_id],
			orderBy: ['experience'],
			order: ['DESC'],
			limit: 10,
		);
		foreach ($dbResult->records() as $dbRecord) {
			$playerExp[] = [
				'bold' => $dbRecord->getInt('account_id') === $oldAccountID ? 'class="bold"' : '',
				'exp' => $dbRecord->getInt('experience'),
				'name' => $dbRecord->getString('player_name'),
			];
		}

		$playerKills = [];
		$dbResult = $db->select(
			'player',
			['game_id' => $game_id],
			orderBy: ['kills'],
			order: ['DESC'],
			limit: 10,
		);
		foreach ($dbResult->records() as $dbRecord) {
			$playerKills[] = [
				'bold' => $dbRecord->getInt('account_id') === $oldAccountID ? 'class="bold"' : '',
				'kills' => $dbRecord->getInt('kills'),
				'name' => $dbRecord->getString('player_name'),
			];
		}

		//now for the alliance stuff
		$allianceExp = [];
		$dbResult = $db->read('SELECT SUM(experience) as exp, alliance_name, alliance_id
					FROM player JOIN alliance USING (game_id, alliance_id)
					WHERE game_id = :game_id GROUP BY alliance_id ORDER BY exp DESC LIMIT 10', [
			'game_id' => $db->escapeNumber($game_id),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$alliance = htmlentities($dbRecord->getString('alliance_name'));
			$id = $dbRecord->getInt('alliance_id');
			$container = new AllianceDetail($this->historyDatabase, $this->historyGameID, $this->historyGameName, $id, $this);
			$allianceExp[] = [
				'bold' => $dbRecord->getInt('alliance_id') === $oldAllianceID ? 'class="bold"' : '',
				'exp' => $dbRecord->getInt('exp'),
				'link' => create_link($container, $alliance),
			];
		}

		$allianceKills = [];
		$dbResult = $db->select(
			'alliance',
			['game_id' => $game_id],
			['kills', 'alliance_name', 'alliance_id'],
			orderBy: ['kills'],
			order: ['DESC'],
			limit: 10,
		);
		foreach ($dbResult->records() as $dbRecord) {
			$alliance = htmlentities($dbRecord->getString('alliance_name'));
			$id = $dbRecord->getInt('alliance_id');
			$container = new AllianceDetail($this->historyDatabase, $this->historyGameID, $this->historyGameName, $id, $this);
			$allianceKills[] = [
				'bold' => $dbRecord->getInt('alliance_id') === $oldAllianceID ? 'class="bold"' : '',
				'kills' => $dbRecord->getInt('kills'),
				'link' => create_link($container, $alliance),
			];
		}

		$template->pageRenderer = fn() => SummaryRenderer::render(
			GameName: $game_name,
			Start: $startDate,
			End: $endDate,
			Type: $gameType,
			Speed: $gameSpeed,
			NumPlayers: $numPlayers,
			MaxExp: $maxExp,
			MaxAlign: $maxAlign,
			MinAlign: $minAlign,
			NumAlliances: $db->count('alliance', ['game_id' => $game_id]),
			PlayerExp: $playerExp,
			PlayerKills: $playerKills,
			AllianceExp: $allianceExp,
			AllianceKills: $allianceKills,
		);
	}

}
