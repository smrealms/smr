<?

$PHP_OUTPUT.= '<div align=center>';
$smarty->assign('PageTopic','DRINKING');

$db2 = new SMR_DB();
if ($player->getCredits() < 10)
{
	$PHP_OUTPUT.=create_echo_error('Come back when you get some money!');
	return;
}
$player->decreaseCredits(10);
$player->update();

if (isset($var['action']) && $var['action'] != 'drink') {

	$PHP_OUTPUT.= 'You ask the bartender for some water and you quickly down it.<br>You dont feel quite so intoxicated anymore.<br>';
	$db2->query('DELETE FROM player_has_drinks WHERE game_id=' . $player->getGameID() . ' AND account_id=' . $player->getAccountID() . ' LIMIT 1');

}
else {

	$random = mt_rand(1, 20);
    //only get Azool or Spock drink if they are very lucky
	if ($random != 1)
		$db->query('SELECT * FROM bar_drink WHERE drink_id != 1 && drink_id != 11 ORDER BY rand() LIMIT 1');
	else
		$db->query('SELECT * FROM bar_drink ORDER BY rand() LIMIT 1');

	if ($db->next_record()) {

		$drink_name = $db->f('drink_name');
		$drink_id = $db->f('drink_id');

        $db2->query('SELECT * FROM player_has_drinks WHERE game_id = '.SmrSession::$game_id.' ORDER by drink_id DESC LIMIT 1');
        if ($db2->next_record())
        	$curr_drink_id = $db2->f('drink_id') + 1;
        else
        	$curr_drink_id = 1;

		if ($drink_id != 11 && $drink_id !=1) {

			$PHP_OUTPUT.=('You have bought a '.$drink_name.' for $10');
			$db2->query('INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ('.$player->getAccountID().', '.SmrSession::$game_id.', '.$curr_drink_id.', '.TIME.')');

		} else {

			$PHP_OUTPUT.=('The bartender says, Ive got something special for ya.<br>');
			$PHP_OUTPUT.=('The bartender turns around for a minute and whips up a '.$drink_name.'.<br>');

			if ($drink_id == 1) $PHP_OUTPUT.=('The bartender says that Spock himself gave him the directions to make this drink.<br>');

			$PHP_OUTPUT.=('You drink the '.$drink_name.' and feel like like you have been drinking for hours.<br>');

			if ($drink_id == 11) $PHP_OUTPUT.=('After drinking the $drink_name you feel like nothing can bring you down and like you are the best trader in the universe.<br>');

			//has the power of 2 drinks
			$db2->query('INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ('.$player->getAccountID().', '.SmrSession::$game_id.', '.$curr_drink_id.', '.TIME.')');
			$curr_drink_id++;
            $db2->query('INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ('.$player->getAccountID().', '.SmrSession::$game_id.', '.$curr_drink_id.', '.TIME.')');

		}

	}

	$db->query('SELECT * FROM player_has_drinks WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . $player->getAccountID());
	$num_drinks = $db->nf();
	//display woozy message
	$PHP_OUTPUT.= '<br>You feel a little W';
	for ($i = 1; $i <= $num_drinks; ++$i) $PHP_OUTPUT.= 'oO';
	$PHP_OUTPUT.= 'zy<br>';
}

//see if the player blacksout or not
if ($num_drinks > 15)
{

	$percent = mt_rand(1,25);
	$lost_credits = round($player->getCredits() * $percent / 100);

	$PHP_OUTPUT.= '<span class="red">You decide you need to go to the restroom.  So you stand up and try to start walking but immediately collapse!<br>About 10 minutes later you wake up and find yourself missing ' . number_format($lost_credits) . ' credits</span><br>';

	$player->decreaseCredits($lost_credits);
	$player->update();

	$db->query('DELETE FROM player_has_drinks WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . $player->getAccountID());

}
$player->increaseHOF(1,'drinks');
$PHP_OUTPUT.= '</div>';

//offer another drink and such
include(get_file_loc('bar_opening.php'));

?>