<?php

function checkForLottoWinner($gameID) {
	global $db;
	// we check for a lotto winner...
	$db->lockTable('player_has_ticket');
	$lottoInfo = getLottoInfo($gameID);
	$lottoWon = false;

	if ($lottoInfo['TimeRemaining'] <= 0) {
		//we need to pick a winner
		$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$db->escapeNumber($gameID).' AND time > 0 ORDER BY rand() LIMIT 1');
		if ($db->nextRecord()) {
			$winner_id = $db->getField('account_id');
			$time = $db->getField('time');
		}

		// Any unclaimed prizes get merged into this prize
		$db->query('SELECT SUM(prize) FROM player_has_ticket WHERE time = 0 AND game_id = '.$db->escapeNumber($gameID));
		if ($db->nextRecord()) {
			$lottoInfo['Prize'] += $db->getField('SUM(prize)');
		}

		// Delete all tickets and re-insert the winning ticket
		$db->query('DELETE FROM player_has_ticket WHERE game_id = '.$db->escapeNumber($gameID));
		$db->query('INSERT INTO player_has_ticket (game_id, account_id, time, prize) '
		           .'VALUES ('.$db->escapeNumber($gameID).','.$db->escapeNumber($winner_id).',\'0\','.$db->escapeNumber($lottoInfo['Prize']).')');

		//get around locked table problem
		$lottoWon = true;
	}
	$db->unlock();

	if ($lottoWon === true) {
		// create news msg
		$winner =& SmrPlayer::getPlayer($winner_id, $gameID);
		$winner->increaseHOF($lottoInfo['Prize'],array('Bar','Lotto','Money','Winnings'), HOF_PUBLIC);
		$winner->increaseHOF(1,array('Bar','Lotto','Results','Wins'), HOF_PUBLIC);
		$news_message = '<span class="yellow">'.$winner->getPlayerName().'</span> has won the lotto! The jackpot was ' . number_format($lottoInfo['Prize']) . '. <span class="yellow">'.$winner->getPlayerName().'</span> can report to any bar to claim their prize before the next drawing!';
		// insert the news entry
		$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = '.$db->escapeNumber($gameID));
		$db->query('INSERT INTO news
					(game_id, time, news_message, type, dead_id, dead_alliance)
					VALUES ('.$db->escapeNumber($gameID).', ' . $db->escapeNumber(TIME) . ', ' . $db->escape_string($news_message, false) . ',\'lotto\','.$db->escapeNumber($winner->getAccountID()).','.$db->escapeNumber($winner->getAllianceID()).')');
	}
}

function getLottoInfo($gameID) {
	global $db;
	$amount = 1000000;
		$firstBuy = TIME;
	$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket
				WHERE game_id = '.$db->escapeNumber($gameID).' AND time > 0');
	$db->nextRecord();
	if ($db->getField('num') > 0) {
		$amount += $db->getField('num') * 1000000 * .9;
		$firstBuy = $db->getField('time');
	}
	//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
	return array('Prize' => $amount, 'TimeRemaining' => $firstBuy + 2 * 86400 - TIME);
}

?>
