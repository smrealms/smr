<?php

$message = '';
//check if we really are a winner
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND ' .
			'account_id = '.$player->getAccountID().' AND time = 0');
while ($db->nextRecord())
{
	$prize = $db->getField('prize');
	$NHLAmount = ($prize - 1000000) / 9;
	$db->query('UPDATE player SET bank = bank + '.$NHLAmount.' WHERE account_id = '.ACCOUNT_ID_NHL.' AND game_id = '.$player->getGameID());
	$player->increaseCredits($prize);
	$player->increaseHOF($prize,array('Bar','Lotto','Money','Claimed'), HOF_PUBLIC);
	$player->increaseHOF(1,array('Bar','Lotto','Results','Claims'), HOF_PUBLIC);
	$message .= '<div align="center">You have claimed <span class="red">$' . number_format($prize) . '</span>!<br /></div><br />';
	$db->query('DELETE FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND ' .
			'account_id = '.$player->getAccountID().' AND prize = '.$prize.' AND time = 0 LIMIT 1');
	$news_message = '<span class="yellow">'.$player->getPlayerName().'</span> has won the lotto!  The jackpot was ' . number_format($prize) . '.  <span class="yellow">'.$player->getPlayerName().'</span> can report to any bar to claim his prize!';
	$db->query('DELETE FROM news WHERE news_message = '.$db->escapeString($news_message).' AND game_id = '.$player->getGameID());
}
//offer another drink and such
$container=create_container('skeleton.php','bar_main.php');
$container['script']='bar_opening.php';
$container['message'] = $message;
forward($container);
?>