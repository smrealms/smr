<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Request;
use Smr\Template;

class GameNews extends HistoryPage {

	public string $file = 'history_games_news.php';

	protected function buildHistory(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Game News : ' . $this->historyGameName);
		$this->addMenu($template);

		$min = Request::getInt('min', 1);
		$max = Request::getInt('max', 50);
		$template->assign('Max', $max);
		$template->assign('Min', $min);

		$template->assign('ShowHREF', $this->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($this->historyGameID) . ' AND news_id >= ' . $db->escapeNumber($min) . ' AND news_id <= ' . $db->escapeNumber($max));
		$rows = [];
		foreach ($dbResult->records() as $dbRecord) {
			$rows[] = [
				'time' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
				'news' => $dbRecord->getString('message'),
			];
		}
		$template->assign('Rows', $rows);
	}

}
