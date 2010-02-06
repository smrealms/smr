<?php

$template->assign('PageTopic','Anonymous accounts for '.$player->getPlayerName());

include(get_file_loc('menue.inc'));
create_trader_menue();

$PHP_OUTPUT.=('<br /><br />');
$db->query('SELECT * FROM anon_bank WHERE owner_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->getNumRows()) {

    $PHP_OUTPUT.=('You own the following accounts<br /><br />');
	while ($db->nextRecord())
	{
		$acc_id = $db->getField('anon_id');
    	$pass = $db->getField('password');
	    $PHP_OUTPUT.=('Account <font color=yellow>'.$acc_id.'</font> with password <font color=yellow>'.$pass.'</font><br />');
    }

} else
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
	
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND account_id = ' .
			$player->getAccountID().' AND time > 0');
$tickets = $db->getNumRows();
$PHP_OUTPUT.=('<br />You own <font color=yellow>'.$tickets.'</font> Lotto Tickets.<br />There are '.$lottoInfo['TimeRemaining'].' remaining until the drawing.');
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND time > 0');
$tickets_tot = $db->getNumRows();
if ($tickets_tot > 0)
{
	$chance = round(($tickets / $tickets_tot) * 100,2);
	$PHP_OUTPUT.=('<br />Currently you have a '.$chance.' % chance to win.');
	
}
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND account_id = ' .
			$player->getAccountID().' AND time = 0');
$tickets = $db->getNumRows();
if ($tickets > 0)
	$PHP_OUTPUT.=('<br /><br />You currently own '.$tickets.' winning tickets.  You should go to the bar to claim your prize.');
?>