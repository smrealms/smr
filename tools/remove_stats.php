#!/usr/bin/php -q
<?

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');

$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM player_has_stats WHERE game_id = 20');
while ($db->next_record())
{
	$planet_busts = $db->f('planet_busts');
	$planet_bust_levels = $db->f('planet_bust_levels');
	$port_raids = $db->f('port_raids');
	$port_raid_level = $db->f('port_raid_levels');
	$sectors_explored = $db->f('sectors_explored');
	$deaths = $db->f('deaths');
	$kills = $db->f('kills');
	$goods_traded = $db->f('goods_traded');
	$experience_traded = $db->f('experience_traded');
	$bounties_claimed = $db->f('bounties_claimed');
	$bounty_amount_claimed = $db->f('bounty_amount_claimed');
	$military_claimed = $db->f('military_claimed');
	$bounty_amount_on = $db->f('bounty_amount_on');
	$player_damage = $db->f('player_damage');
	$port_damage = $db->f('port_damage');
	$planet_damage = $db->f('planet_damage');
	$turns_used = $db->f('turns_used');
	$kill_exp = $db->f('kill_exp');
	$traders_killed_exp = $db->f('traders_killed_exp');
	$blackjack_win = $db->f('blackjack_win');
	$blackjack_lose = $db->f('blackjack_lose');
	$lotto = $db->f('lotto');
	$drinks = $db->f('drinks');
	$trade_profit = $db->f('trade_profit');
	$trade_sales = $db->f('trade_sales');
	$mines = $db->f('mines');
	$combat_drones = $db->f('combat_drones');
	$scout_drones = $db->f('scout_drones');
	$money_gained = $db->f('money_gained');
	$killed_ships = $db->f('killed_ships');
	$died_ships = $db->f('died_ships');
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
				'WHERE account_id = ' . $db->f('account_id') . ' LIMIT 1;'.EOL);

}

?>
