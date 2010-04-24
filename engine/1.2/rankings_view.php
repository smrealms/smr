<?php

$rank_id = $account->get_rank();

print_topic("Extended User Rankings");
include(get_file_loc('menue.inc'));
if ($session ->game_id != 0)
	print_trader_menue();

$db->query("SELECT * FROM rankings WHERE rankings_id = $rank_id");
if ($db->next_record())
	$rank_name = $db->f("rankings_name");

// initialize vars
$kills = 0;
$exp = 0;

// get stats
$db->query("SELECT * from account_has_stats WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record()) {

	$kills = ($db->f("kills") > 0) ? $db->f("kills") : 0;
	$exp = ($db->f("experience_traded") > 0) ? $db->f("experience_traded") : 0;

}

print("You have <font color=\"red\">$kills</font> kills and <font color=\"red\">$exp</font> traded experience<br><br>");
print("You are ranked as a <font size=\"4\" color=\"greenyellow\">$rank_name</font> player.<p><br>");
$db->query("SELECT * FROM rankings");
$i = 0;
while ($db->next_record()) {

    if ($i > 1)
    	print(" OR " . $db->f(experience_needed) . " experience OR " . $db->f(kills_needed) . " kills.");
    if ($i > 0)
    	print("<br>");
    print($db->f(rankings_name) . " - ");
    print($db->f(kills_needed) . " kills and " . $db->f(experience_needed) . " experience");
	$i++;
}
$db2 = new SmrMySqlDatabase();
print("<br><br>");
$db->query("SELECT * FROM account_has_stats WHERE account_id = $account->account_id");
if ($db->next_record()) {

	print("<b>Extended Stats</b><br>");
	print("You have joined " . $db->f("games_joined") . " games.<br>");
	print("You have busted " . $db->f("planet_busts") . " planets.<br>");
	print("You have busted a total of " . $db->f("planet_bust_levels") . " combined levels on planets.<br>");
	print("You have raided " . $db->f("port_raids") . " ports.<br>");
	print("You have raided a total of " . $db->f("port_raid_levels") . " combined levels of ports.<br>");
	print("You have done " . $db->f("planet_damage") . " damage to planets.<br>");
	print("You have done " . $db->f("port_damage") . " damage to ports.<br>");
	print("You have explored " . $db->f("sectors_explored") . " sectors.<br>");
	print("You have died " . $db->f("deaths") . " times.<br>");
	print("You have traded " . $db->f("goods_traded") . " goods.<br>");
	$db2->query("SELECT sum(amount) as amount FROM account_donated WHERE account_id = $account->account_id");
	if ($db2->next_record())
	    print("You have donated " . $db2->f("amount") . " dollars to SMR.<br>");
	print("You have claimed " . $db->f("bounties_claimed") . " bounties.<br>");
	print("You have claimed " . $db->f("bounty_amount_claimed") . " credits from bounties.<br>");
	print("You have claimed " . $db->f("military_claimed") . " credits from military payment.<br>");
	print("You have had a total of " . $db->f("bounty_amount_on") . " credits bounty placed on you.<br>");
	print("You have done " . $db->f("player_damage") . " damage to other ships.<br>");
	print("The total experience of traders you have killed is " . $db->f("traders_killed_exp") . ".<br>");
	print("You have gained " . $db->f("kill_exp") . " experience from killing other traders.<br>");
	print("You have used " . $db->f("turns_used") . " turns since your last death.<br>");
	print("You have won " . $db->f("blackjack_win") . " credits from Blackjack.<br>");
	print("You have lost " . $db->f("blackjack_lose") . " credits from Blackjack.<br>");
	print("You have won " . $db->f("lotto") . " credits from the lotto.<br>");
	print("You have had " . $db->f("drinks") . " drinks at the bar.<br>");
	print("You have bought " . $db->f("mines") . " mines.<br>");
	print("You have bought " . $db->f("combat_drones") . " combat_drones.<br>");
	print("You have bought " . $db->f("scout_drones") . " scout_drones.<br>");
	print("You have has gained " . $db->f("money_gained") . " credits from killing.<br>");
	print("You have has killed " . $db->f("killed_ships") . " credits worth of ships.<br>");
	print("You have has lost " . $db->f("died_ships") . " credits worth of ships.<br>");

}

//current game stats
print("<br><br>");
if (empty($player)) $game_id = 0;
else $game_id = $player->game_id;
$db->query("SELECT * FROM player_has_stats WHERE account_id = $account->account_id AND game_id = $game_id");
if ($db->next_record()) {

	print("<b>Current Game Extended Stats</b><br>");
	print("You have busted " . $db->f("planet_busts") . " planets.<br>");
	print("You have busted a total of " . $db->f("planet_bust_levels") . " combined levels on planets.<br>");
	print("You have raided " . $db->f("port_raids") . " ports.<br>");
	print("You have raided a total of " . $db->f("port_raid_levels") . " combined levels of ports.<br>");
	print("You have done " . $db->f("planet_damage") . " damage to planets.<br>");
	print("You have done " . $db->f("port_damage") . " damage to ports.<br>");
	print("You have explored " . $db->f("sectors_explored") . " sectors.<br>");
	print("You have died " . $db->f("deaths") . " times.<br>");
	print("You have traded " . $db->f("goods_traded") . " goods.<br>");
	print("You have claimed " . $db->f("bounties_claimed") . " bounties.<br>");
	print("You have claimed " . $db->f("bounty_amount_claimed") . " credits from bounties.<br>");
	print("You have claimed " . $db->f("military_claimed") . " credits from military payment.<br>");
	print("You have had a total of " . $db->f("bounty_amount_on") . " credits bounty placed on you.<br>");
	print("You have done " . $db->f("player_damage") . " damage to other ships.<br>");
	print("The total experience of traders you have killed is " . $db->f("traders_killed_exp") . ".<br>");
	print("You have gained " . $db->f("kill_exp") . " experience from killing other traders.<br>");
	print("You have used " . $db->f("turns_used") . " turns since your last death.<br>");
	print("You have won " . $db->f("blackjack_win") . " credits from Blackjack.<br>");
	print("You have lost " . $db->f("blackjack_lose") . " credits from Blackjack.<br>");
	print("You have won " . $db->f("lotto") . " credits from the lotto.<br>");
	print("You have had " . $db->f("drinks") . " drinks at the bar.<br>");

}

?>
