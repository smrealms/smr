<?php
require_once(get_file_loc("smr_history_db.inc"));
function cust_round($x) {
	return round($x/10)*10;
}

$acc_id = $var["acc_id"];
//view rankings of other players
$this_acc = new SMR_ACCOUNT();
$this_acc->get_by_id($acc_id);
$rank_id = $this_acc->get_rank();
$game_id = $var["game_id"];
$hof_name = stripslashes($this_acc->HoF_name);
$db->query("SELECT * FROM rankings WHERE rankings_id = $rank_id");
if ($db->next_record())
	$rank_name = $db->f("rankings_name");

// initialize vars
$kills = 0;
$exp = 0;

// get stats
$db->query("SELECT * from account_has_stats_cache WHERE account_id = $this_acc->account_id");
if ($db->next_record()) {
	$kills = ($db->f("kills") > 0) ? $db->f("kills") : 0;
	$exp = ($db->f("experience_traded") > 0) ? $db->f("experience_traded") : 0;
}
if ($var['sending_page'] == 'hof') {
	print_topic("Extended User Rankings for $hof_name");
	print("$hof_name has <font color=\"red\">$kills</font> kills and <font color=\"red\">$exp</font> traded experience<br><br>");
	print("$hof_name is ranked as a <font size=\"4\" color=\"greenyellow\">$rank_name</font> player.<br><br>");
	
	$db2 = new SmrMySqlDatabase();
	$db->query("SELECT * FROM account_has_stats_cache WHERE account_id = $this_acc->account_id");
	if ($db->next_record()) {
	
		print("<b>Extended Stats</b><br>");
		print("$hof_name has joined " . $db->f("games_joined") . " games.<br>");
		print("$hof_name has busted " . $db->f("planet_busts") . " planets.<br>");
		print("$hof_name has busted a total of " . $db->f("planet_bust_levels") . " combined levels on planets.<br>");
		print("$hof_name has raided " . $db->f("port_raids") . " ports.<br>");
		print("$hof_name has raided a total of " . $db->f("port_raid_levels") . " combined levels of ports.<br>");
		print("$hof_name has done " . $db->f("planet_damage") . " damage to planets.<br>");
		print("$hof_name has done " . $db->f("port_damage") . " damage to ports.<br>");
		print("$hof_name has explored " . $db->f("sectors_explored") . " sectors.<br>");
		print("$hof_name has died " . $db->f("deaths") . " times.<br>");
		print("$hof_name has traded " . $db->f("goods_traded") . " goods.<br>");
		$db2->query("SELECT sum(amount) as amount FROM account_donated WHERE account_id = $this_acc->account_id");
		if ($db2->next_record())
		    print("$hof_name has donated " . $db2->f("amount") . " dollars to SMR.<br>");
		print("$hof_name has claimed " . $db->f("bounties_claimed") . " bounties.<br>");
		print("$hof_name has claimed " . $db->f("bounty_amount_claimed") . " credits from bounties.<br>");
		print("$hof_name has claimed " . $db->f("military_claimed") . " credits from military payment.<br>");
		print("$hof_name has had a total of " . $db->f("bounty_amount_on") . " credits bounty placed on him/her.<br>");
		print("$hof_name has done " . $db->f("player_damage") . " damage to other ships.<br>");
		print("The total experience of traders $this_acc->HoF_name has killed is " . $db->f("traders_killed_exp") . ".<br>");
		print("$hof_name has gained " . $db->f("kill_exp") . " experience from killing other traders.<br>");
		print("$hof_name has approximately used " . cust_round($db->f("turns_used")) . " turns since his/her last death.<br>");
		print("$hof_name has won " . $db->f("blackjack_win") . " credits from Blackjack.<br>");
		print("$hof_name has lost " . $db->f("blackjack_lose") . " credits from Blackjack.<br>");
		print("$hof_name has won " . $db->f("lotto") . " credits from the lotto.<br>");
		print("$hof_name has had " . $db->f("drinks") . " drinks at the bar.<br>");
		print("$hof_name has bought " . $db->f("mines") . " mines.<br>");
		print("$hof_name has bought " . $db->f("combat_drones") . " combat drones.<br>");
		print("$hof_name has bought " . $db->f("scout_drones") . " scout drones.<br>");
		print("$hof_name has gained " . $db->f("money_gained") . " credits from killing.<br>");
		print("$hof_name has killed " . $db->f("killed_ships") . " credits worth of ships.<br>");
		print("$hof_name has lost " . $db->f("died_ships") . " credits worth of ships.<br>");
	}
} else {
	
	//current game stats
	$db2 = new SMR_HISTORY_DB();
	$db2->query("SELECT * FROM game WHERE game_id = $game_id");
	//if next record we have an old game so we query the hist db
	if ($db2->next_record()) {
	
		$db = new SMR_HISTORY_DB();
		$past = "Yes";
	
	} else $db = new SmrMySqlDatabase();
	$db->query("SELECT * FROM player WHERE game_id = $game_id AND account_id = $acc_id");
	if ($db->next_record()) $playerName = stripslashes($db->f("player_name"));
	else $playerName = 'Unknown Player';
	print_topic("Current Game Stats for $playerName");
	$db->query("SELECT * FROM player_has_stats_cache WHERE account_id = $this_acc->account_id AND game_id = $game_id");
	if ($db->next_record()) {
		print("$playerName is ranked as a <font size=\"4\" color=\"greenyellow\">$rank_name</font> player.<br><br>");
		print("<b>Current Game Extended Stats</b><br>");
		print("$playerName has killed " . $db->f("kills") . " traders.<br>");
		print("$playerName has traded " . $db->f("experience_traded") . " experience.<br>");
		print("$playerName has busted " . $db->f("planet_busts") . " planets.<br>");
		print("$playerName has busted a total of " . $db->f("planet_bust_levels") . " combined levels on planets.<br>");
		print("$playerName has raided " . $db->f("port_raids") . " ports.<br>");
		print("$playerName has raided a total of " . $db->f("port_raid_levels") . " combined levels of ports.<br>");
		print("$playerName has done " . $db->f("planet_damage") . " damage to planets.<br>");
		print("$playerName has done " . $db->f("port_damage") . " damage to ports.<br>");
		print("$playerName has explored " . $db->f("sectors_explored") . " sectors.<br>");
		print("$playerName has died " . $db->f("deaths") . " times.<br>");
		print("$playerName has traded " . $db->f("goods_traded") . " goods.<br>");
		print("$playerName has claimed " . $db->f("bounties_claimed") . " bounties.<br>");
		print("$playerName has claimed " . $db->f("bounty_amount_claimed") . " credits from bounties.<br>");
		print("$playerName has claimed " . $db->f("military_claimed") . " credits from military payment.<br>");
		print("$playerName has had a total of " . $db->f("bounty_amount_on") . " credits bounty placed on him/her.<br>");
		print("$playerName has done " . $db->f("player_damage") . " damage to other ships.<br>");
		print("The total experience of traders $playerName has killed is " . $db->f("traders_killed_exp") . ".<br>");
		print("$playerName has gained " . $db->f("kill_exp") . " experience from killing other traders.<br>");
		print("$playerName has used " . $db->f("turns_used") . " turns since his/her last death.<br>");
		print("$playerName has won " . $db->f("blackjack_win") . " credits from Blackjack.<br>");
		print("$playerName has lost " . $db->f("blackjack_lose") . " credits from Blackjack.<br>");
		print("$playerName has won " . $db->f("lotto") . " credits from the lotto.<br>");
		print("$playerName has had " . $db->f("drinks") . " drinks at the bar.<br>");
		print("$playerName has bought " . $db->f("mines") . " mines.<br>");
		print("$playerName has bought " . $db->f("combat_drones") . " combat drones.<br>");
		print("$playerName has bought " . $db->f("scout_drones") . " scout drones.<br>");
		print("$playerName has gained " . $db->f("money_gained") . " credits from killing.<br>");
		print("$playerName has killed " . $db->f("killed_ships") . " credits worth of ships.<br>");
		print("$playerName has lost " . $db->f("died_ships") . " credits worth of ships.<br>");
	}
}
//this is needed to make the rest of loader function
//FIXME: just rename the hof variable sometime, after reviewing, rewriting the whole page might be best.
$db = new SmrMySqlDatabase();
?>
