<?php

echo '<div align=center>';
print_topic('DRINKING');

$db2 = new SmrMySqlDatabase();
$time = time();
if ($player->credits < 10)
{
	print_error('Come back when you get some money!');
	return;
}
$player->credits -= 10;
$player->update();

if (isset($var['action']) && $var['action'] != 'drink') {

	echo 'You ask the bartender for some water and you quickly down it.<br>You dont feel quite so intoxicated anymore.<br>';
	$db2->query('DELETE FROM player_has_drinks WHERE game_id=' . $player->game_id . ' AND account_id=' . $player->account_id . ' LIMIT 1');

}
else {

	$random = mt_rand(1, 20);
    //only get Azool or Spock drink if they are very lucky
	if ($random != 1)
		$db->query("SELECT * FROM bar_drink WHERE drink_id != 1 && drink_id != 11 ORDER BY rand() LIMIT 1");
	else
		$db->query("SELECT * FROM bar_drink ORDER BY rand() LIMIT 1");

	if ($db->next_record()) {

		$drink_name = $db->f("drink_name");
		$drink_id = $db->f("drink_id");

        $db2->query("SELECT * FROM player_has_drinks WHERE game_id = ".SmrSession::$game_id." ORDER by drink_id DESC LIMIT 1");
        if ($db2->next_record())
        	$curr_drink_id = $db2->f("drink_id") + 1;
        else
        	$curr_drink_id = 1;

		if ($drink_id != 11 && $drink_id !=1) {

			print("You have bought a $drink_name for $10");
			$db2->query("INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ($player->account_id, ".SmrSession::$game_id.", $curr_drink_id, $time)");

		} else {

			print("The bartender says, Ive got something special for ya.<br>");
			print("The bartender turns around for a minute and whips up a $drink_name.<br>");

			if ($drink_id == 1) print("The bartender says that Spock himself gave him the directions to make this drink.<br>");

			print("You drink the $drink_name and feel like like you have been drinking for hours.<br>");

			if ($drink_id == 11) print("After drinking the $drink_name you feel like nothing can bring you down and like you are the best trader in the universe.<br>");

			//has the power of 2 drinks
			$db2->query("INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ($player->account_id, ".SmrSession::$game_id.", $curr_drink_id, $time)");
			$curr_drink_id++;
            $db2->query("INSERT INTO player_has_drinks (account_id, game_id, drink_id, time) VALUES ($player->account_id, ".SmrSession::$game_id.", $curr_drink_id, $time)");

		}

	}

	$db->query('SELECT * FROM player_has_drinks WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . $player->account_id);
	$num_drinks = $db->nf();
	//display woozy message
	echo '<br>You feel a little W';
	for ($i = 1; $i <= $num_drinks; ++$i) echo 'oO';
	echo 'zy<br>';
}

//see if the player blacksout or not
if ($num_drinks > 15) {

	$percent = mt_rand(1,25);
	$lost_credits = round($player->credits * $percent / 100);

	echo '<span class="red">You decide you need to go to the restroom.  So you stand up and try to start walking but immediately collapse!<br>About 10 minutes later you wake up and find yourself missing ' . number_format($lost_credits) . ' credits</span><br>';

	$player->credits -= $lost_credits;
	$player->update();

	$db->query('DELETE FROM player_has_drinks WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . $player->account_id);

}
$player->update_stat('drinks', 1);
echo '</div>';

//offer another drink and such
include(get_file_loc("bar_opening.php"));

?>