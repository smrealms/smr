<?

//check if we really are a winner
$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND ' . 
			'account_id = '.$player->getAccountID().' AND time = 0');
$PHP_OUTPUT.=('<div align=center>');
while ($db->next_record()) {
	
	$prize = $db->f('prize');
	$NHLAmount = ($prize - 1000000) / 9;
	$NHLAccount = 13;
	$db->query('UPDATE player SET bank = bank + '.$NHLAmount.' WHERE account_id = '.$NHLAccount.' AND game_id = '.$player->getGameID());
	$player->increaseCredits($prize);
	$player->update();
	$player->increaseHOF($prize,'lotto');
	$PHP_OUTPUT.=('You have claimed <font color=red>$' . number_format($prize) . '</font>!<br>');
	$db->query('DELETE FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND ' . 
			'account_id = '.$player->getAccountID().' AND prize = '.$prize.' AND time = 0 LIMIT 1');
	$news_message = '<font color=yellow>'.$player->getPlayerName().'</font> has won the lotto!  The jackpot was ' . number_format($prize) . '.  <font color=yellow>'.$player->getPlayerName().'</font> can report to any bar to claim his prize!';
	$db->query('DELETE FROM news WHERE news_message = '.$db->escapeString($news_message).' AND game_id = '.$player->getGameID());
	
}
$PHP_OUTPUT.=('</div><br>');
//offer another drink and such
include(get_file_loc('bar_opening.php'));

?>