<?

$smarty->assign('PageTopic','Anonymous accounts for '.$player->getPlayerName());

include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_trader_menue();

$PHP_OUTPUT.=('<br><br>');
$db->query('SELECT * FROM anon_bank WHERE owner_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->nf()) {

    $PHP_OUTPUT.=('You own the following accounts<br><br>');
	while ($db->next_record()) {

		$acc_id = $db->f('anon_id');
    	$pass = $db->f('password');
	    $PHP_OUTPUT.=('Account <font color=yellow>'.$acc_id.'</font> with password <font color=yellow>'.$pass.'</font><br>');

    }

} else
	$PHP_OUTPUT.=('You own no anonymous accounts<br>');

$time = time();
$db->lock('player_has_ticket');
$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket WHERE ' . 
			'game_id = '.$player->getGameID().' AND time > 0 GROUP BY game_id ORDER BY time DESC');
$db->next_record();
if ($db->f('num') > 0) {
	$amount = ($db->f('num') * 1000000 * .9) + 1000000;
	$first_buy = $db->f('time');
} else {
	$amount = 1000000;
	$first_buy = time();
}
//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)

$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - $time;

if ($time_rem <= 0)
{
	//we need to pick a winner
	$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' ORDER BY rand()');
	if ($db->next_record()) {
		$winner_id = $db->f('account_id');
		$time = $db->f('time');
	}
	$db->query('SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
	if ($db->next_record()) {
		
		$amount += $db->f('prize');
		$db->query('DELETE FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
		
	}
	$db->query('SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID().' AND account_id = '.$winner_id);
	$db->query('UPDATE player_has_ticket SET time = 0, prize = '.$amount.' WHERE time = '.$time.' AND ' .
					'account_id = '.$winner_id.' AND game_id = '.$player->getGameID());
	//delete losers
	$db->query('DELETE FROM player_has_ticket WHERE time > 0 AND game_id = '.$player->getGameID());
	//get around locked table problem
	$val = 1;
	$first_buy =time();
	$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - $time;
}
$db->unlock();
if ($val == 1) {
	// create news msg
	$winner =& SmrPlayer::getPlayer($winner_id, $player->getGameID());
	$news_message = '<font color=yellow>'.$winner->player_name.'</font> has won the lotto!  The jackpot was ' . number_format($amount) . '.  <font color=yellow>'.$winner->player_name.'</font> can report to any bar to claim his prize!';
	// insert the news entry
	$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = '.$player->getGameID());
	$db->query('INSERT INTO news ' .
	'(game_id, time, news_message, type) ' .
	'VALUES('.$player->getGameID().', ' . TIME . ', ' . $db->escape_string($news_message, false) . ',\'lotto\')');
	
}
$smarty->assign('PageTopic','Lotto Tickets for '.$player->getPlayerName());
$days = floor($time_rem / 60 / 60 / 24);
$time_rem -= $days * 60 * 60 * 24;
$hours = floor($time_rem / 60 / 60);
$time_rem -= $hours * 60 * 60;
$mins = floor($time_rem / 60);
$time_rem -= $mins * 60;
$secs = $time_rem;
$time_rem = '<b>'.$days.' Days, '.$hours.' Hours, '.$mins.' Minutes, and '.$secs.' Seconds</b>';
	
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND account_id = ' .
			$player->getAccountID().' AND time > 0');
$tickets = $db->nf();
$PHP_OUTPUT.=('<br>You own <font color=yellow>'.$tickets.'</font> Lotto Tickets.<br>There are '.$time_rem.' remaining until the drawing.');
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND time > 0');
$tickets_tot = $db->nf();
if ($tickets_tot > 0) {
	
	$chance = round(($tickets / $tickets_tot) * 100,2);
	$PHP_OUTPUT.=('<br>Currently you have a '.$chance.' % chance to win.');
	$PHP_OUTPUT.=('<br>');
	
}
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND account_id = ' .
			$player->getAccountID().' AND time = 0');
$tickets = $db->nf();
if ($tickets > 0)
$PHP_OUTPUT.=('You currently own '.$tickets.' winning tickets.  You should go to the bar to claim your prize.');
?>