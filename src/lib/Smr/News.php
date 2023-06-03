<?php declare(strict_types=1);

namespace Smr;

/**
 * Collection of functions to help with displaying news.
 */
class News {

	/**
	 * Takes a populated query and returns the news items.
	 *
	 * @return array<array{Date: string, Message: string}>
	 */
	public static function getNewsItems(DatabaseResult $dbResult): array {
		$session = Session::getInstance();
		$account = $session->getAccount();

		$newsItems = [];
		foreach ($dbResult->records() as $dbRecord) {
			$message = bbify(
				$dbRecord->getString('news_message'),
				$dbRecord->getInt('game_id'),
			);
			if ($dbRecord->getString('type') === 'admin') {
				$message = '<span class="admin">ADMIN </span>' . $message;
			}
			$newsItems[] = [
				'Date' => date($account->getDateTimeFormatSplit(), $dbRecord->getInt('time')),
				'Message' => $message,
			];
		}
		return $newsItems;
	}

	public static function doBreakingNewsAssign(int $gameID): void {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND type = \'breaking\' AND time > :breaking_news_time  ORDER BY time DESC LIMIT 1', [
			'game_id' => $db->escapeNumber($gameID),
			'breaking_news_time' => $db->escapeNumber(Epoch::time() - TIME_FOR_BREAKING_NEWS),
		]);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$template = Template::getInstance();
			$template->assign('BreakingNews', [
				'Time' => $dbRecord->getInt('time'),
				'Message' => bbify($dbRecord->getString('news_message'), $gameID),
			]);
		}
	}

	public static function doLottoNewsAssign(int $gameID): void {
		Lotto::checkForLottoWinner($gameID);
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND type = \'lotto\' ORDER BY time DESC LIMIT 1', [
			'game_id' => $db->escapeNumber($gameID),
		]);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$template = Template::getInstance();
			$template->assign('LottoNews', [
				'Time' => $dbRecord->getInt('time'),
				'Message' => bbify($dbRecord->getString('news_message'), $gameID),
			]);
		}
	}

}
