<?php

print_topic("CURRENT NEWS");
include(get_file_loc('menue.inc'));
print_news_menue();
//we we check for a lotto winner...
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
$db->unlock();
//end lotto check
$curr_allowed = $player->last_news_update;
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "news_read.php";
$container["breaking"] = "yes";
$var_del = time() - 86400;
$db->query("DELETE FROM news WHERE time < $var_del AND type = 'breaking'");
$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type = 'breaking' ORDER BY time DESC LIMIT 1");
if ($db->next_record()) {

    $time = $db->f("time");
    print_link($container, "<b>MAJOR NEWS! - " . date("n/j/Y g:i:s A", $time) . "</b>");
    print("<br><br>");

}
if (isset($var["breaking"])) {

    $db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type = 'breaking' ORDER BY time DESC LIMIT 1");
    $text = stripslashes($db->f("news_message"));
    $time = $db->f("time");
    print_table();
    print("<tr>");
    print("<th align=\"center\"><span style=\"color:#80C870;\">Time</span></th>");
    print("<th align=\"center\"><span style=\"color:#80C870;\">Breaking News</span></th>");
    print("</tr>");
    print("<tr>");
    print("<td align=\"center\"> " . date("n/j/Y g:i:s A", $time) . " </td>");
    print("<td align=\"left\">$text</td>");
    print("</tr>");
    print("</table>");
    print("<br><br>");

}
//display lottonews if we have it
$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type = 'lotto' ORDER BY time DESC");
while ($db->next_record()) {
	print_table();
    print("<tr>");
    print("<th align=\"center\"><span style=\"color:#80C870;\">Time</span></th>");
    print("<th align=\"center\"><span style=\"color:#80C870;\">Message</span></th>");
    print("</tr>");
    print("<tr>");
    $time = $db->f("time");
    print("<td align=\"center\"> " . date("n/j/Y g:i:s A", $time) . " </td>");
    print("<td align=\"left\">");
    $db->p("news_message");
    print("</td>");
    print("</tr>");
    print("</table>");
	print("<br><br>");
}
$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND time > $curr_allowed AND type = 'regular' ORDER BY news_id DESC");
$player->last_news_update = time();
$player->update();

if ($db->nf()) {

    print("<b><big><div align=\"center\" style=\"color:blue;\">You have " . $db->nf() . " news entries.</div></big></b>");
    print_table();
    print("<tr>");
    print("<th align=\"center\">Time</span>");
    print("<th align=\"center\">News</span>");
    print("</tr>");

    while ($db->next_record()) {

        $time = $db->f("time");
        $news = stripslashes($db->f("news_message"));

        print("<tr>");
        print("<td align=\"center\">" . date("n/j/Y g:i:s A", $time) . "</td>");
        print("<td align=\"left\">$news</td>");
        print("</tr>");

    }

    print("</table>");

} else
    print("You have no current news.");

?>