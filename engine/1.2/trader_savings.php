<?php

print_topic("Anonymous accounts for $player->player_name");

include(get_file_loc('menue.inc'));
print_trader_menue();

print("<br><br>");
$db->query("SELECT * FROM anon_bank WHERE owner_id = $player->account_id AND game_id = $player->game_id");
if ($db->nf()) {

    print("You own the following accounts<br><br>");
	while ($db->next_record()) {

		$acc_id = $db->f("anon_id");
    	$pass = $db->f("password");
	    print("Account <font color=yellow>$acc_id</font> with password <font color=yellow>$pass</font><br>");

    }

} else
	print("You own no anonymous accounts<br>");

$time = time();
$db->lock("player_has_ticket");
$db->query("SELECT count(*) as num, min(time) as time FROM player_has_ticket WHERE " . 
			"game_id = $player->game_id AND time > 0 GROUP BY game_id ORDER BY time DESC");
$db->next_record();
if ($db->f("num") > 0) {
	$amount = ($db->f("num") * 1000000 * .9) + 1000000;
	$first_buy = $db->f("time");
} else {
	$amount = 1000000;
	$first_buy = time();
}
//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)

$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - $time;

if ($time_rem <= 0) {
	//we need to pick a winner
	$db->query("SELECT * FROM player_has_ticket WHERE game_id = $player->game_id ORDER BY rand()");
	if ($db->next_record()) {
		$winner_id = $db->f("account_id");
		$time = $db->f("time");
	}
	$db->query("SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = $player->game_id");
	if ($db->next_record()) {
		
		$amount += $db->f("prize");
		$db->query("DELETE FROM player_has_ticket WHERE time = 0 AND game_id = $player->game_id");
		
	}
	$db->query("SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = $player->game_id AND account_id = $winner_id");
	$db->query("UPDATE player_has_ticket SET time = 0, prize = $amount WHERE time = $time AND " .
					"account_id = $winner_id AND game_id = $player->game_id");
	//delete losers
	$db->query("DELETE FROM player_has_ticket WHERE time > 0 AND game_id = $player->game_id");
	//get around locked table problem
	$val = 1;
	$first_buy =time();
	$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - $time;
}
$db->unlock();
if ($val == 1) {
	// create news msg
	$winner = new SMR_PLAYER($winner_id, $player->game_id);
	$news_message = "<font color=yellow>$winner->player_name</font> has won the lotto!  The jackpot was " . number_format($amount) . ".  <font color=yellow>$winner->player_name</font> can report to any bar to claim his prize!";
	// insert the news entry
	$db->query("DELETE FROM news WHERE type = 'lotto' AND game_id = $player->game_id");
	$db->query("INSERT INTO news " .
	"(game_id, time, news_message, type) " .
	"VALUES($player->game_id, " . time() . ", " . format_string($news_message, false) . ",'lotto')");
	
}
print_topic("Lotto Tickets for $player->player_name");
$days = floor($time_rem / 60 / 60 / 24);
$time_rem -= $days * 60 * 60 * 24;
$hours = floor($time_rem / 60 / 60);
$time_rem -= $hours * 60 * 60;
$mins = floor($time_rem / 60);
$time_rem -= $mins * 60;
$secs = $time_rem;
$time_rem = "<b>$days Days, $hours Hours, $mins Minutes, and $secs Seconds</b>";
	
$db->query("SELECT * FROM player_has_ticket WHERE game_id = $player->game_id AND account_id = " .
			"$player->account_id AND time > 0");
$tickets = $db->nf();
print("<br>You own <font color=yellow>$tickets</font> Lotto Tickets.<br>There are $time_rem remaining until the drawing.");
$db->query("SELECT * FROM player_has_ticket WHERE game_id = $player->game_id AND time > 0");
$tickets_tot = $db->nf();
if ($tickets_tot > 0) {
	
	$chance = round(($tickets / $tickets_tot) * 100,2);
	print("<br>Currently you have a $chance % chance to win.");
	print("<br>");
	
}
$db->query("SELECT * FROM player_has_ticket WHERE game_id = $player->game_id AND account_id = " .
			"$player->account_id AND time = 0");
$tickets = $db->nf();
if ($tickets > 0)
print("You currently own $tickets winning tickets.  You should go to the bar to claim your prize.");
?>