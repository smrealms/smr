<?php

print_topic("HALL OF FAME");

print("Welcome to the Hall of Fame $player->player_name!<br>The Hall of Fame is a comprehensive ");
print("list of player accomplishments. Here you can view how players rank in many different ");
print("aspects of the game rather than just kills, deaths, and experience with the rankings system.<br>");
print("The Hall of Fame is updated only once every 24 hours on midnight.<br>");

//put em in a table so they look kinda nice
print_table();
print("<tr>");
print("<th align=\"center\">Type</th>");
print("<th align=\"center\">Options</th>");
print("</tr>");

//let them choose what they want to view...all container info is used below
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "hall_of_fame_detail.php";
transfer("type");

$container["id"] = "donation";
$container["display_first"] = "Rankings by amount of money donated to Space Merchant Relams";
$container["display_second"] = "Amount of Money Donated";
print_form($container);
print("<tr>");
print("<td align=\"center\" nowrap><b>Ranks by Money Donated to <i>Space Merchant Realms</i></b></td>");
print("<td align=\"center\">");
print_submit("Overall");
print("</td></tr>");
print("</form>");

$container["id"] = "kills";
$container["display_first"] = "Rankings by number of kills";
$container["display_second"] = "Number of Kills";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Kills</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("&nbsp;");
print_submit("Per Death");
print("</td></tr>");
print("</form>");

$container["id"] = "deaths";
$container["display_first"] = "Rankings by number of deaths";
$container["display_second"] = "Number of Deaths";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Deaths</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "planet_busts";
$container["display_first"] = "Rankings by number of planet busts";
$container["display_second"] = "Number of Planet Busts";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Planet Busts</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "planet_bust_levels";
$container["display_first"] = "Rankings by number of planet levels busted";
$container["display_second"] = "Number of Levels Busted on Planets";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Planet Levels Busted</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "port_raids";
$container["display_first"] = "Rankings by number of ports raided";
$container["display_second"] = "Number of Ports Raided";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Port Raids</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "port_raid_levels";
$container["display_first"] = "Rankings by number of port levels raided";
$container["display_second"] = "Number of Port Levels Raided";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Port Levels Raided</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "sectors_explored";
$container["display_first"] = "Rankings by sectors explored";
$container["display_second"] = "Number of Sectors Explored";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Sectors Explored</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "goods_traded";
$container["display_first"] = "Rankings by number of goods traded";
$container["display_second"] = "Number of Goods Traded";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Goods Traded</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "experience_traded";
$container["display_first"] = "Rankings by amount of experience traded";
$container["display_second"] = "Amount of Experience Traded";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Experience Traded</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("&nbsp;");
print_submit("Per Good Traded");
print("</td></tr>");
print("</form>");

$container["id"] = "bounties_claimed";
$container["display_first"] = "Rankings by number of bounties claimed.";
$container["display_second"] = "Number of bounties claimed";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Bounties Collected</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "bounty_amount_claimed";
$container["display_first"] = "Rankings by amount of bounties collected.";
$container["display_second"] = "Amount of bounties collected";
$container["per"] = "";
$container["special"] = "no";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Credits Collected From Bounties</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "bounty_amount_on";
$container["display_first"] = "Rankings by amount of credits from bounties placed on a player.";
$container["display_second"] = "Amount of credits";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Bounties Placed on a Player</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "military_claimed";
$container["display_first"] = "Rankings by amount of military payment claimed.";
$container["display_second"] = "Amount of military payment claimed";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Military Payment Claimed</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "bonus_turns";
$container["display_first"] = "Rankings by bonus turns gained by voting for Space Merchant Realms";
$container["display_second"] = "Number of Bonus Turns Gained";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Bonus Turns Gained by Voting</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("</td></tr>");
print("</form>");

$container["id"] = "player_damage";
$container["display_first"] = "Rankings by damage done to other players";
$container["display_second"] = "Damage Done";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Damage Done to Other Players</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("&nbsp;");
print_submit("Per Kill");
print("</td></tr>");
print("</form>");

$container["id"] = "planet_damage";
$container["display_first"] = "Rankings by damage done to planets";
$container["display_second"] = "Damage Done";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Damage Done to Planets</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("&nbsp;");
print_submit("Per Planet Bust");
print("</td></tr>");
print("</form>");

$container["id"] = "port_damage";
$container["display_first"] = "Rankings by damage done to ports";
$container["display_second"] = "Damage Done";
print_form($container);
print("<tr>");
print("<td align=\"center\">Ranks by Damage Done to Ports</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("&nbsp;");
print_submit("Per Game");
print("&nbsp;");
print_submit("Per Port Raid");
print("</td></tr>");
print("</form>");

$container["id"] = "player_damage";
$container["display_first"] = "Rankings by";
$container["display_second"] = "";
print_form($container);
print("<tr>");
print("<td align=\"center\">Attacking other Ships</td>");
print("<td align=\"center\">");
print_submit("Experience Gained");
//duplicate
//print("&nbsp;");
//print_submit("Damage Done");
print("<br>");
print_submit("Experience From Killing");
print("<br>");
print_submit("Total Experience of Players Killed");
print("<br>");
print_submit("Average Experience of Players Killed");
print("</td></tr>");
print("</form>");

$container["id"] = "port_damage";
$container["display_first"] = "Rankings by";
$container["display_second"] = "";
print_form($container);
print("<tr>");
print("<td align=\"center\">Attacking Ports</td>");
print("<td align=\"center\">");
print_submit("Experience Gained");
//duplicate
//print("&nbsp;");
//print_submit("Damage Done");
print("</td></tr>");
print("</form>");

$container["id"] = "planet_damage";
$container["display_first"] = "Rankings by";
$container["display_second"] = "";
print_form($container);
print("<tr>");
print("<td align=\"center\">Attacking Planets</td>");
print("<td align=\"center\">");
print_submit("Experience Gained");
//duplicate
//print("&nbsp;");
//print_submit("Damage Done");
print("</td></tr>");
print("</form>");

$container["id"] = "turns_used";
$container["display_first"] = "Rankings by turns used since last death";
$container["display_second"] = "Turns Used";
print_form($container);
print("<tr>");
print("<td align=\"center\">Turns Used Since Last Death</td>");
print("<td align=\"center\">");
print_submit("Overall");
print("</td></tr>");
print("</form>");

print("</table>");

?>