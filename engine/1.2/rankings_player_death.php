<?php

print_topic("DEATH RANKINGS");

include(get_file_loc('menue.inc'));
print_ranking_menue(0, 2);

// what rank are we?
$db->query("SELECT * FROM player WHERE game_id = ".SmrSession::$game_id." AND " .
                                      "(deaths > $player->deaths OR " .
                                      "(deaths = $player->deaths AND player_name <= " . format_string("$player->player_name", true) . " ))");
$our_rank = $db->nf();

// how many players are there?
$db->query("SELECT * FROM player WHERE game_id = $player->game_id");
$total_player = $db->nf();

print("<div align=\"center\">");
print("<p>Here are the rankings of players by their deaths</p>");
print("<p>You are ranked $our_rank out of $total_player</p>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Player</th>");
print("<th>Race</th>");
print("<th>Alliance</th>");
print("<th>Deaths</th>");
print("</tr>");

$db->query("SELECT * FROM player WHERE game_id = $player->game_id ORDER BY deaths DESC, player_name LIMIT 10");

$rank = 0;
while ($db->next_record()) {

    // get current player
    $curr_player = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);

    // increase rank counter
    $rank++;

    print("<tr>");
    print("<td valign=\"top\" align=\"center\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$rank</td>");
    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$curr_player->level_name ");

    $container = array();
    $container["url"]        = "skeleton.php";
    $container["body"]        = "trader_search_result.php";
    $container["player_id"] = $curr_player->player_id;
    print_link($container, $curr_player->get_colored_name());

    print("</td>");
    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$curr_player->race_name</td>");

    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">");
    if ($curr_player->alliance_id > 0) {

        $container = array();
        $container["url"]             = "skeleton.php";
        $container["body"]             = "alliance_roster.php";
        $container["alliance_id"]    = $curr_player->alliance_id;
        print_link($container, "$curr_player->alliance_name");
    } else
        print("(none)");
    print("</td>");
    print("<td valign=\"top\" align=\"right\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">" . number_format($curr_player->deaths) . "</td>");
    print("</tr>");

}

print("</table>");
$action = $_REQUEST['action'];
if ($action == "Show") {

    $min_rank = $_POST["min_rank"];
    $max_rank = $_POST["max_rank"];

} else {

    $min_rank = $our_rank - 5;
    $max_rank = $our_rank + 5;

}

if ($min_rank <= 0) {

    $min_rank = 1;
    $max_rank = 10;

}

if ($max_rank > $total_player)
    $max_rank = $total_player;

$container = array();
$container["url"]		= "skeleton.php";
$container["body"]		= "rankings_player_death.php";
$container["min_rank"]	= $min_rank;
$container["max_rank"]	= $max_rank;

print_form($container);
print("<p><input type=\"text\" name=\"min_rank\" value=\"$min_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;-&nbsp;<input type=\"text\" name=\"max_rank\" value=\"$max_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;");
print_submit("Show");
print("</p></form>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Player</th>");
print("<th>Race</th>");
print("<th>Alliance</th>");
print("<th>Deaths</th>");
print("</tr>");

$db->query("SELECT * FROM player WHERE game_id = ".SmrSession::$game_id." ORDER BY deaths DESC, player_name LIMIT " . ($min_rank - 1) . ", " . ($max_rank - $min_rank + 1));

$rank = $min_rank - 1;
while ($db->next_record()) {

    // get current player
    $curr_player = new SMR_PLAYER($db->f("account_id"), $player->game_id);

    // increase rank counter
    $rank++;

    print("<tr>");
    print("<td valign=\"top\" align=\"center\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$rank</td>");
    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$curr_player->level_name ");

    $container = array();
    $container["url"]        = "skeleton.php";
    $container["body"]        = "trader_search_result.php";
    $container["player_id"] = $curr_player->player_id;
    print_link($container, $curr_player->get_colored_name());

    print("</td>");
    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">$curr_player->race_name</td>");

    print("<td valign=\"top\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">");
    if ($curr_player->alliance_id > 0) {

        $container = array();
        $container["url"]             = "skeleton.php";
        $container["body"]             = "alliance_roster.php";
        $container["alliance_id"]    = $curr_player->alliance_id;
        print_link($container, "$curr_player->alliance_name");
    } else
        print("(none)");
    print("</td>");
    print("<td valign=\"top\" align=\"right\"");
    if ($player->account_id == $curr_player->account_id)
        print(" style=\"font-weight:bold;\"");
    print(">" . number_format($curr_player->deaths) . "</td>");
    print("</tr>");

}

print("</table>");
print("</div>");

?>