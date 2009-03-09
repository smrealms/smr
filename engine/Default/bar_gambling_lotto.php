<?php
//do we have a winner first...
$time = time();
$db->lockTable('player_has_ticket');
$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket WHERE ' . 
		'game_id = '.$player->getGameID().' AND time > 0 GROUP BY game_id ORDER BY time DESC');
$db->nextRecord();
if ($db->getField('num') > 0) {
	$amount = ($db->getField('num') * 1000000 * .9) + 1000000;
	$first_buy = $db->getField('time');
} else {
	$amount = 1000000;
	$first_buy = time();
}
//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
$time_rem = ($first_buy + (2 * 86400)) - $time;
if ($time_rem <= 0) {
	
	//we need to pick a winner
	$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' ORDER BY rand()');
	if ($db->nextRecord()) {
		$winner_id = $db->getField('account_id');
		$time = $db->getField('time');
	}
	$db->query('SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
	if ($db->nextRecord()) {
	
		$amount += $db->getField('prize');
		$db->query('DELETE FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
		
	}
	$db->query('UPDATE player_has_ticket SET time = 0, prize = '.$amount.' WHERE time = '.$time.' AND ' .
				'account_id = '.$winner_id.' AND game_id = '.$player->getGameID());
	//delete losers
	$db->query('DELETE FROM player_has_ticket WHERE time > 0 AND game_id = '.$player->getGameID());
	//get around locked table problem
	$val = 1;

}
$db->unlock();
if ($val == 1)
{
	// create news msg
	$winner =& SmrPlayer::getPlayer($winner_id, $player->getGameID());
	$news_message = '<font color=yellow>'.$winner->getPlayerName().'</font> has won the lotto!  The jackpot was ' . number_format($amount) . '.  <font color=yellow>'.$winner->getPlayerName().'</font> can report to any bar to claim his prize!';
	// insert the news entry delete old first
	$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = '.$player->getGameID());
	$db->query('INSERT INTO news ' .
	'(game_id, time, news_message, type) ' .
	'VALUES('.$player->getGameID().', ' . time() . ', ' . $db->escape_string($news_message, false) . ',\'lotto\')');
	
}
//end do we have winner
$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket WHERE ' . 
			'game_id = '.$player->getGameID().' AND time > 0 GROUP BY game_id ORDER BY time DESC');
$db->nextRecord();
if ($db->getField('num') > 0) {
	$amount = ($db->getField('num') * 1000000 * .9) + 1000000;
	$first_buy = $db->getField('time');
} else {
	$amount = 1000000;
	$first_buy = time();
}
//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - $time;
$days = floor($time_rem / 60 / 60 / 24);
$time_rem -= $days * 60 * 60 * 24;
$hours = floor($time_rem / 60 / 60);
$time_rem -= $hours * 60 * 60;
$mins = floor($time_rem / 60);
$time_rem -= $mins * 60;
$secs = $time_rem;
$time_rem = '<b>'.$days.' Days, '.$hours.' Hours, '.$mins.' Minutes, and '.$secs.' Seconds</b>';
$PHP_OUTPUT.=('<br /><div align=center>Currently '.$time_rem.' remain until the winning ticket');
$PHP_OUTPUT.=(' is drawn, and the prize is $' . number_format($amount) . '.</div><br />');
$PHP_OUTPUT.=('<div align=center>Ahhhh so your interested in the lotto huh?  ');
$PHP_OUTPUT.=('Well here is how it works.  First you will need to buy a ticket, ');
$PHP_OUTPUT.=('they cost $1,000,000 a piece.  Next you need to watch the news.  Once the winning ');
$PHP_OUTPUT.=('lotto ticket is drawn there will be a section in the news with the winner.');
$PHP_OUTPUT.=('  If you win you can come to any bar and claim your prize!');
$PHP_OUTPUT.=('</div><div align=center>');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bar_main.php';
$container['script'] = 'bar_gambling_processing.php';
$container['action'] = 'process';
$PHP_OUTPUT.=create_button($container,'Buy a Ticket ($1,000,000)');
?>
