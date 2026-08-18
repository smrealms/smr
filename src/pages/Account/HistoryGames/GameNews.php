<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Request;
use Smr\Template;

class GameNews extends HistoryPage {

	protected function buildHistory(Account $account, Template $template): void {
		$template->pageTopic = 'Game News : ' . $this->historyGameName;
		$this->addMenu($template);

		$min = Request::getInt('min', 1);
		$max = Request::getInt('max', 50);
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND news_id >= :min_news_id AND news_id <= :max_news_id', [
			'game_id' => $db->escapeNumber($this->historyGameID),
			'min_news_id' => $db->escapeNumber($min),
			'max_news_id' => $db->escapeNumber($max),
		]);
		$rows = [];
		foreach ($dbResult->records() as $dbRecord) {
			$rows[] = [
				'time' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
				'news' => $dbRecord->getString('message'),
			];
		}
		$template->pageRenderer = fn() => GameNewsRenderer::render(
			Max: $max,
			Min: $min,
			ShowHREF: $this->href(),
			Rows: $rows,
		);
	}

}
