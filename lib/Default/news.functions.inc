<?php

/**
 * Takes a populated query and returns the news items.
 */
function getNewsItems(SmrMySqlDatabase $db) {
	$newsItems = [];
	while ($db->nextRecord()) {
		$message = bbifyMessage($db->getField('news_message'));
		if ($db->getField('type') == 'admin') {
			$message = '<span class="admin">ADMIN </span>' . $message;
		}
		$newsItems[] = [
			'Date' => date(DATE_FULL_SHORT_SPLIT, $db->getInt('time')),
			'Message' => $message,
		];
	}
	return $newsItems;
}

function doBreakingNewsAssign($gameID,&$template) {
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM news WHERE game_id = '.$db->escapeNumber($gameID).' AND type = \'breaking\' AND time > '.$db->escapeNumber(TIME - TIME_FOR_BREAKING_NEWS).' ORDER BY time DESC LIMIT 1');
	if ($db->nextRecord()) {
		$template->assign('BreakingNews',array('Time' => $db->getField('time'), 'Message' => bbifyMessage($db->getField('news_message'))));
	}
}

function doLottoNewsAssign($gameID,&$template) {
	require_once(get_file_loc('bar.functions.inc'));
	checkForLottoWinner($gameID);
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM news WHERE game_id = '.$db->escapeNumber($gameID).' AND type = \'lotto\' ORDER BY time DESC LIMIT 1');
	if($db->nextRecord()) {
		 $template->assign('LottoNews',array('Time' => $db->getField('time'), 'Message' => bbifyMessage($db->getField('news_message'))));
	}
}
