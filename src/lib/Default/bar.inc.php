<?php declare(strict_types=1);

function checkForLottoWinner($gameID) {

	// No more lotto winners after the game has ended
	if (SmrGame::getGame($gameID)->hasEnded()) {
		return;
	}

	// we check for a lotto winner...
	$db = MySqlDatabase::getInstance();
	$db->lockTable('player_has_ticket');
	$lottoInfo = getLottoInfo($gameID);

	if ($lottoInfo['TimeRemaining'] > 0) {
		// Drawing is not closed yet
		$db->unlock();
		return;
	}

	//we need to pick a winner
	$db->query('SELECT * FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > 0 ORDER BY rand() LIMIT 1');
	$db->requireRecord();
	$winner_id = $db->getInt('account_id');

	// Any unclaimed prizes get merged into this prize
	$db->query('SELECT SUM(prize) FROM player_has_ticket WHERE time = 0 AND game_id = ' . $db->escapeNumber($gameID));
	if ($db->nextRecord()) {
		$lottoInfo['Prize'] += $db->getInt('SUM(prize)');
	}

	// Delete all tickets and re-insert the winning ticket
	$db->query('DELETE FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($gameID));
	$db->query('INSERT INTO player_has_ticket (game_id, account_id, time, prize) '
	           .'VALUES (' . $db->escapeNumber($gameID) . ',' . $db->escapeNumber($winner_id) . ',\'0\',' . $db->escapeNumber($lottoInfo['Prize']) . ')');

	$db->unlock();

	// create news msg
	$winner = SmrPlayer::getPlayer($winner_id, $gameID);
	$winner->increaseHOF($lottoInfo['Prize'], array('Bar', 'Lotto', 'Money', 'Winnings'), HOF_PUBLIC);
	$winner->increaseHOF(1, array('Bar', 'Lotto', 'Results', 'Wins'), HOF_PUBLIC);
	$news_message = $winner->getBBLink() . ' has won the lotto! The jackpot was ' . number_format($lottoInfo['Prize']) . '. ' . $winner->getBBLink() . ' can report to any bar to claim their prize before the next drawing!';
	// insert the news entry
	$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = ' . $db->escapeNumber($gameID));
	$db->query('INSERT INTO news
				(game_id, time, news_message, type, dead_id, dead_alliance)
				VALUES ('.$db->escapeNumber($gameID) . ', ' . $db->escapeNumber(Smr\Epoch::time()) . ', ' . $db->escapeString($news_message) . ',\'lotto\',' . $db->escapeNumber($winner->getAccountID()) . ',' . $db->escapeNumber($winner->getAllianceID()) . ')');
}

function getLottoInfo($gameID) {
	$amount = 1000000;
	$firstBuy = Smr\Epoch::time();

	$db = MySqlDatabase::getInstance();
	$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket
				WHERE game_id = '.$db->escapeNumber($gameID) . ' AND time > 0');
	$db->requireRecord();
	if ($db->getInt('num') > 0) {
		$amount += $db->getInt('num') * 1000000 * .9;
		$firstBuy = $db->getInt('time');
	}
	//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
	return array('Prize' => $amount, 'TimeRemaining' => $firstBuy + TIME_LOTTO - Smr\Epoch::time());
}
