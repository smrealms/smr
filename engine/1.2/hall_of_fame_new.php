<?php

function category($name, $options, $row) {

	global $var;
	$i = 0;
	//table name thing goes here
	print("<tr>");
	print("<td align=center>$name</td>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "hall_of_fame_new_detail.php";
	$container["category"] = $name;
	$container["row"] = $row;
	if (isset($var["game_id"]))
		$container["game_id"] = $var["game_id"];
	print_form($container);
	print("<td align=center valign=middle>");
	foreach($options as $print) {
		
		$i++;
		list($one, $two) = split (",", $print);
		if (isset($two)) print("<input type=hidden name=mod[] value=\"$print\">");
		print_submit($one);
		print("&nbsp;");
		if ($i % 3 == 0) print("<br>");
		//unset vars for next sub cat
		unset($one, $two);
		
	}
	print("</td></form></tr>");

}
if (isset($var["game_id"])) $game_id = $var["game_id"];
$base = array();

if (empty($game_id)) {
	
	$base[] = "Overall";
	$base[] = "Per Game, / games_joined";
	$topic = "All Time Hall of Fame";

} else {
	
	$base[] = "Total";
	$db->query("SELECT * FROM game WHERE game_id = $game_id");
	if ($db->next_record()) {
		
		$name = $db->f("game_name");
		$topic = "$name Hall of Fame";
		
	} else $topic = "Somegame Hall of Fame";
	
}
print("<div align=center>");

print_topic("$topic");

print("Welcome to the Hall of Fame " . stripslashes($account->HoF_name) . "!<br>The Hall of Fame is a comprehensive ");
print("list of player accomplishments.  Here you can view how players rank in many different ");
print("aspects of the game rather than just kills, deaths, and experience with the rankings system.<br>");
print("The Hall of Fame is updated only once every 24 hours on midnight.<br>");

print_table();
print("<tr><th align=center>Category</th><th align=center width=60%>Subcategory</th></tr>");

//category(Display,Array containing subcategories (info after , is the info for sql),stat name in db)
if (empty($game_id))
	category("<b>Money Donated to SMR</b>", array("Overall"), "Not Needed");
category("Kills", array_merge($base,array("Per Death, / deaths")), "kills");
category("Deaths", $base, "deaths");
category("Planet Busts", $base, "planet_busts");
category("Planet Levels Busted", $base, "planet_bust_levels");
category("Damage Done to Planets", array_merge($base,array("Experience Gained, / 4")), "planet_damage");
category("Port Raids", $base, "port_raids");
category("Port Levels Raided", $base, "port_raid_levels");
category("Damage Done to Ports", array_merge($base,array("Experience Gained, / 20")), "port_damage");
category("Sectors Explored", $base, "sectors_explored");
category("Goods Traded", $base, "goods_traded");
category("Trade Profit", array_merge($base,array("Per Good Traded, / goods_traded", "Per Experience Traded, / experience_traded")), "trade_profit");
category("Trade Sales", array_merge($base,array("Per Good Traded, / goods_traded", "Per Experience Traded, / experience_traded")), "trade_sales");
category("Experience Traded", array_merge($base,array("Per Good Traded, / goods_traded")), "experience_traded");
category("Bounties Collected", $base, "bounties_claimed");
category("Credits from Bounties Collected", array_merge($base,array("Per Bounty Claimed, / bounties_claimed")), "bounty_amount_claimed");
category("Bounties Place on Player", $base, "bounty_amount_on");
category("Military Payment Claimed", $base, "military_claimed");
category("Damage Done to Other Players", array_merge($base,array("Per Kill, / kills","Experience Gained, / 4")), "player_damage");
category("Experience Gained from Killing", array_merge($base,array("Per Kill, / kills")), "kill_exp");
category("Money Gained from Killing", $base, "money_gained");
category("Experience of Players Killed", array_merge($base,array("Per Kill, / kills")), "traders_killed_exp");
category("Cost of Ships Killed", $base, "killed_ships");
category("Cost of Ships Died In", $base, "died_ships");
category("Mines Bought", $base, "mines");
category("Combat Drones Bought", $base, "combat_drones");
category("Scout Drones", $base, "scout_drones");
category("Forces Bought", $base, "mines + combat_drones + scout_drones");
category("Blackjack Winnings", array_merge($base,array("To Losings, / blackjack_lose")), "blackjack_win");
category("Blackjack Loses", $base, "blackjack_lose");
category("Lotto Winnings", $base, "lotto");
category("Drinks at Bars", $base, "drinks");
category("Turns Since Last Death", $base, "turns_used");

print("</table></div>");

?>