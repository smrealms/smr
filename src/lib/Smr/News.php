<?php declare(strict_types=1);

namespace Smr;

/**
 * Collection of functions to help with displaying news.
 */
class News {

	/**
	 * Takes a populated query and returns the news items.
	 */
	public static function getNewsItems(DatabaseResult $dbResult) : array {
		$session = Session::getInstance();
		$account = $session->getAccount();

		$newsItems = [];
		foreach ($dbResult->records() as $dbRecord) {
			$message = bbifyMessage($dbRecord->getString('news_message'));
			if ($dbRecord->getField('type') == 'admin') {
				$message = '<span class="admin">ADMIN </span>' . $message;
			}
			$newsItems[] = [
				'Date' => date($account->getDateTimeFormatSplit(), $dbRecord->getInt('time')),
				'Message' => $message,
			];
		}
		return $newsItems;
	}

	public static function doBreakingNewsAssign(int $gameID) : void {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND type = \'breaking\' AND time > ' . $db->escapeNumber(Epoch::time() - TIME_FOR_BREAKING_NEWS) . ' ORDER BY time DESC LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$template = Template::getInstance();
			$template->assign('BreakingNews', array('Time' => $dbRecord->getInt('time'), 'Message' => bbifyMessage($dbRecord->getString('news_message'))));
		}
	}

	public static function doLottoNewsAssign(int $gameID) : void {
		require_once(get_file_loc('bar.inc.php'));
		checkForLottoWinner($gameID);
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND type = \'lotto\' ORDER BY time DESC LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$template = Template::getInstance();
			$template->assign('LottoNews', array('Time' => $dbRecord->getInt('time'), 'Message' => bbifyMessage($dbRecord->getString('news_message'))));
		}
	}

}
