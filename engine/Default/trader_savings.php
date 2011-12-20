<?php

$template->assign('PageTopic','Anonymous accounts for '.$player->getPlayerName());

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$PHP_OUTPUT.=('<br /><br />');
$db->query('SELECT * FROM anon_bank WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->getNumRows()) {
	$PHP_OUTPUT.=('You own the following accounts<br /><br />');
	while ($db->nextRecord()) {
		$acc_id = $db->getField('anon_id');
		$pass = $db->getField('password');
		$PHP_OUTPUT.=('Account <span class="yellow">'.$acc_id.'</span> with password <span class="yellow">'.$pass.'</span><br />');
	}
}
else
	$PHP_OUTPUT.=('You own no anonymous accounts<br />');

require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());
$lottoInfo = getLottoInfo($player->getGameID());

$template->assign('PageTopic','Lotto Tickets for '.$player->getPlayerName());
$days = floor($lottoInfo['TimeRemaining'] / 60 / 60 / 24);
$lottoInfo['TimeRemaining'] -= $days * 60 * 60 * 24;
$hours = floor($lottoInfo['TimeRemaining'] / 60 / 60);
$lottoInfo['TimeRemaining'] -= $hours * 60 * 60;
$mins = floor($lottoInfo['TimeRemaining'] / 60);
$lottoInfo['TimeRemaining'] -= $mins * 60;
$secs = $lottoInfo['TimeRemaining'];
$lottoInfo['TimeRemaining'] = '<b>'.$days.' Days, '.$hours.' Hours, '.$mins.' Minutes, and '.$secs.' Seconds</b>';
	
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND time > 0');
$db->nextRecord();
$tickets = $db->getInt('count(*)');
$PHP_OUTPUT.=('<br />You own <span class="yellow">'.$tickets.'</span> Lotto Tickets.<br />There are '.$lottoInfo['TimeRemaining'].' remaining until the drawing.');
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND time > 0');
$db->nextRecord();
$tickets_tot = $db->getInt('count(*)');
if ($tickets_tot > 0) {
	$chance = round(($tickets / $tickets_tot) * 100,2);
	$PHP_OUTPUT.=('<br />Currently you have a '.$chance.' % chance to win.');
	
}
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' .
			$player->getAccountID().' AND time = 0');
$db->nextRecord();
$winningTickets = $db->getInt('count(*)');
if ($winningTickets > 0)
	$PHP_OUTPUT.=('<br /><br />You currently own '.$winningTickets.' winning tickets.  You should go to the bar to claim your prize.');
?>