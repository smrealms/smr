#!/usr/bin/php -q
<?php

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');

$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM player_has_stats WHERE game_id = 20');
while ($db->nextRecord())
{
	$planet_busts = $db->getField('planet_busts');
	$planet_bust_levels = $db->getField('planet_bust_levels');
	$port_raids = $db->getField('port_raids');
	$port_raid_level = $db->getField('port_raid_levels');
	$sectors_explored = $db->getField('sectors_explored');
	$deaths = $db->getField('deaths');
	$kills = $db->getField('kills');
	$goods_traded = $db->getField('goods_traded');
	$experience_traded = $db->getField('experience_traded');
	$bounties_claimed = $db->getField('bounties_claimed');
	$bounty_amount_claimed = $db->getField('bounty_amount_claimed');
	$military_claimed = $db->getField('military_claimed');
	$bounty_amount_on = $db->getField('bounty_amount_on');
	$player_damage = $db->getField('player_damage');
	$port_damage = $db->getField('port_damage');
	$planet_damage = $db->getField('planet_damage');
	$turns_used = $db->getField('turns_used');
	$kill_exp = $db->getField('kill_exp');
	$traders_killed_exp = $db->getField('traders_killed_exp');
	$blackjack_win = $db->getField('blackjack_win');
	$blackjack_lose = $db->getField('blackjack_lose');
	$lotto = $db->getField('lotto');
	$drinks = $db->getField('drinks');
	$trade_profit = $db->getField('trade_profit');
	$trade_sales = $db->getField('trade_sales');
	$mines = $db->getField('mines');
	$combat_drones = $db->getField('combat_drones');
	$scout_drones = $db->getField('scout_drones');
	$money_gained = $db->getField('money_gained');
	$killed_ships = $db->getField('killed_ships');
	$died_ships = $db->getField('died_ships');
		$PHP_OUTPUT.=('UPDATE account_has_stats SET ' . 
				'planet_busts = planet_busts - ' . $planet_busts . ', ' .
				'planet_bust_levels = planet_bust_levels - ' . $planet_bust_levels . ', ' .
				'port_raids = port_raids - ' . $port_raids . ', ' .
				'port_raid_levels = port_raid_levels - ' . $port_raid_level . ', ' .
				'sectors_explored = sectors_explored - ' . $sectors_explored . ', ' .
				'deaths = deaths - ' . $deaths . ', ' .
				'kills = kills - ' . $kills . ', ' .
				'goods_traded = goods_traded - ' . $goods_traded . ', ' .
				'experience_traded = experience_traded - ' . $experience_traded . ', ' .
				'bounties_claimed = bounties_claimed - ' . $bounties_claimed . ', ' .
				'bounty_amount_claimed = bounty_amount_claimed - ' . $bounty_amount_claimed . ', ' .
				'military_claimed = military_claimed - ' . $military_claimed . ', ' .
				'bounty_amount_on = bounty_amount_on - ' . $bounty_amount_on . ', ' .
				'player_damage = player_damage - ' . $player_damage . ', ' .
				'port_damage = port_damage - ' . $port_damage . ', ' .
				'planet_damage = planet_damage - ' . $planet_damage . ', ' .
				'turns_used = turns_used - ' . $turns_used . ', ' .
				'kill_exp = kill_exp - ' . $kill_exp . ', ' .
				'traders_killed_exp = traders_killed_exp - ' . $traders_killed_exp . ', ' .
				'blackjack_win = blackjack_win - ' . $blackjack_win . ', ' .
				'blackjack_lose = blackjack_lose - ' . $blackjack_lose . ', ' .
				'lotto = lotto - ' . $lotto . ', ' .
				'drinks = drinks - ' . $drinks . ', ' .
				'trade_profit = trade_profit - ' . $trade_profit . ', ' .
				'trade_sales = trade_sales - ' . $trade_sales . ', ' .
				'mines = mines - ' . $mines . ', ' .
				'combat_drones = combat_drones - ' . $combat_drones . ', ' .
				'scout_drones = scout_drones - ' . $scout_drones . ', ' .
				'money_gained = money_gained - ' . $money_gained . ', ' .
				'killed_ships = killed_ships - ' . $killed_ships . ', ' .
				'died_ships = died_ships - ' . $died_ships . ' ' .
				'WHERE account_id = ' . $db->getField('account_id') . ' LIMIT 1;'.EOL);

}
