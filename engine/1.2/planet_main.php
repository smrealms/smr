<?php
		require_once(get_file_loc("smr_planet.inc"));
if ($player->land_on_planet == "FALSE") {
	
	print_error("You are not on a planet!");
	return;
	
}

// create planet object
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
$planet->build();
print_topic("PLANET : $planet->planet_name [SECTOR #$player->sector_id]");

include(get_file_loc('menue.inc'));
print_planet_menue();

//print the dump cargo message or other message.
if (isset($var["msg"]))
   print($var["msg"] . "<br>");


print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
print("<tr>");
print("<th width=\"125\">&nbsp;</th>");
print("<th width=\"75\">Current</th>");
print("<th width=\"75\">Max</th>");
print("</tr>");

print("<tr>");
print("<td>Generator</td><td align=\"center\">");
print($planet->construction[1]);
print("</td><td align=\"center\">");
print($planet->max_construction[1]);
print("</td>");
print("</tr>");

print("<tr>");
print("<td>Hangar</td><td align=\"center\">");
print($planet->construction[2]);
print("</td><td align=\"center\">");
print($planet->max_construction[2]);
print("</td>");
print("</tr>");

print("<tr>");
print("<td>Turret</td><td align=\"center\">");
print($planet->construction[3]);
print("</td><td align=\"center\">");
print($planet->max_construction[3]);
print("</td>");
print("</tr>");

print("</table>");

print("<br />");

print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
print("<tr>");
print("<th width=\"125\">&nbsp;</th>");
print("<th width=\"75\">Amount</th>");
print("<th width=\"75\">Accuracy</th>");
print("</tr>");

print("<tr>");
print("<td>Shields</td><td align=\"center\">$planet->shields</td><td>&nbsp;</td>");
print("</tr>");

print("<tr>");
print("<td>Combat Drones</td><td align=\"center\">$planet->drones</td><td>&nbsp;</td>");
print("</tr>");

print("<tr>");
print("<td>Turrets</td><td align=\"center\">" . $planet->construction[3] . "</td><td align=\"center\">" . $planet->accuracy() . " %</td>");
print("</tr>");

print("</table>");
print("<br />");

$db->query("SELECT * FROM player WHERE sector_id = $player->sector_id AND " .
                                      "game_id = ".SmrSession::$game_id." AND " .
                                      "account_id != ".SmrSession::$old_account_id." AND " .
                                      "land_on_planet = 'TRUE' " .
                                "ORDER BY last_active DESC");

while ($db->next_record()) {

    $planet_player = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);

    $container = array();
    $container["url"]            = "planet_kick_processing.php";
    $container["account_id"]    = $planet_player->account_id;

    print_form($container);

    $container = array();
    $container["url"]        = "skeleton.php";
    $container["body"]        = "trader_search_result.php";
    $container["player_id"]    = $planet_player->player_id;

    print_link($container, "<span style=\"color:yellow;\">$planet_player->player_name</span>");
    print("&nbsp;");

    // should we be able to kick this player from our rock?
    if (($player->alliance_id != $planet_player->alliance_id || $player->alliance_id == 0) && $planet->owner_id == $player->account_id)
        print_submit("Kick");

    print("</form>");

}
if($db->nf() > 0 ) print("<br>");

print_form(create_container("planet_launch_processing.php", ""));
print_submit("Launch");
print("</form>");

?>