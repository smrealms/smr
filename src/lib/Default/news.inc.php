<?php declare(strict_types=1);

/**
 * Takes a populated query and returns the news items.
 */
function getNewsItems(Smr\Database $db) {
	$session = Smr\Session::getInstance();
	$account = $session->getAccount();

	$newsItems = [];
	while ($db->nextRecord()) {
		$message = bbifyMessage($db->getField('news_message'));
		if ($db->getField('type') == 'admin') {
			$message = '<span class="admin">ADMIN </span>' . $message;
		}
		$newsItems[] = [
			'Date' => date($account->getDateTimeFormatSplit(), $db->getInt('time')),
			'Message' => $message,
		];
	}
	return $newsItems;
}

function doBreakingNewsAssign(int $gameID) : void {
	$db = Smr\Database::getInstance();
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND type = \'breaking\' AND time > ' . $db->escapeNumber(Smr\Epoch::time() - TIME_FOR_BREAKING_NEWS) . ' ORDER BY time DESC LIMIT 1');
	if ($db->nextRecord()) {
		$template = Smr\Template::getInstance();
		$template->assign('BreakingNews', array('Time' => $db->getInt('time'), 'Message' => bbifyMessage($db->getField('news_message'))));
	}
}

function doLottoNewsAssign(int $gameID) : void {
	require_once(get_file_loc('bar.inc.php'));
	checkForLottoWinner($gameID);
	$db = Smr\Database::getInstance();
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND type = \'lotto\' ORDER BY time DESC LIMIT 1');
	if ($db->nextRecord()) {
		$template = Smr\Template::getInstance();
		$template->assign('LottoNews', array('Time' => $db->getInt('time'), 'Message' => bbifyMessage($db->getField('news_message'))));
	}
}
