<?

$rank_id = $account->get_rank();

$template->assign('PageTopic','Extended User Rankings');
include(get_file_loc('menue.inc'));
if (SmrSession::$game_id != 0)
	$PHP_OUTPUT.=create_trader_menue();

$db->query('SELECT * FROM rankings WHERE rankings_id = '.$rank_id);
if ($db->nextRecord())
	$rank_name = $db->getField('rankings_name');

// initialize vars
$kills = 0;
$exp = 0;

// get stats
$db->query('SELECT * from account_has_stats WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord()) {

	$kills = ($db->getField('kills') > 0) ? $db->getField('kills') : 0;
	$exp = ($db->getField('experience_traded') > 0) ? $db->getField('experience_traded') : 0;

}

$PHP_OUTPUT.=('You have <font color="red">'.$kills.'</font> kills and <font color="red">'.$exp.'</font> traded experience<br /><br />');
$PHP_OUTPUT.=('You are ranked as a <font size="4" color="greenyellow">'.$rank_name.'</font> player.<p><br />');
$db->query('SELECT * FROM rankings');
$i = 0;
while ($db->nextRecord()) {

    if ($i > 1)
    	$PHP_OUTPUT.=(' OR ' . $db->getField(experience_needed) . ' experience OR ' . $db->getField(kills_needed) . ' kills.');
    if ($i > 0)
    	$PHP_OUTPUT.=('<br />');
    $PHP_OUTPUT.=($db->getField(rankings_name) . ' - ');
    $PHP_OUTPUT.=($db->getField(kills_needed) . ' kills and ' . $db->getField(experience_needed) . ' experience');
	$i++;
}
$db2 = new SmrMySqlDatabase();
$PHP_OUTPUT.=('<br /><br />');
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.$account->account_id);
if ($db->nextRecord()) {

	$PHP_OUTPUT.=('<b>Extended Stats</b><br />');
	$PHP_OUTPUT.=('You have joined ' . $db->getField('games_joined') . ' games.<br />');
	$PHP_OUTPUT.=('You have busted ' . $db->getField('planet_busts') . ' planets.<br />');
	$PHP_OUTPUT.=('You have busted a total of ' . $db->getField('planet_bust_levels') . ' combined levels on planets.<br />');
	$PHP_OUTPUT.=('You have raided ' . $db->getField('port_raids') . ' ports.<br />');
	$PHP_OUTPUT.=('You have raided a total of ' . $db->getField('port_raid_levels') . ' combined levels of ports.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('planet_damage') . ' damage to planets.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('port_damage') . ' damage to ports.<br />');
	$PHP_OUTPUT.=('You have explored ' . $db->getField('sectors_explored') . ' sectors.<br />');
	$PHP_OUTPUT.=('You have died ' . $db->getField('deaths') . ' times.<br />');
	$PHP_OUTPUT.=('You have traded ' . $db->getField('goods_traded') . ' goods.<br />');
	$db2->query('SELECT sum(amount) as amount FROM account_donated WHERE account_id = '.$account->account_id);
	if ($db2->nextRecord())
	    $PHP_OUTPUT.=('You have donated ' . $db2->getField('amount') . ' dollars to SMR.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('bounties_claimed') . ' bounties.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('bounty_amount_claimed') . ' credits from bounties.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('military_claimed') . ' credits from military payment.<br />');
	$PHP_OUTPUT.=('You have had a total of ' . $db->getField('bounty_amount_on') . ' credits bounty placed on you.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('player_damage') . ' damage to other ships.<br />');
	$PHP_OUTPUT.=('The total experience of traders you have killed is ' . $db->getField('traders_killed_exp') . '.<br />');
	$PHP_OUTPUT.=('You have gained ' . $db->getField('kill_exp') . ' experience from killing other traders.<br />');
	$PHP_OUTPUT.=('You have used ' . $db->getField('turns_used') . ' turns since your last death.<br />');
	$PHP_OUTPUT.=('You have won ' . $db->getField('blackjack_win') . ' credits from Blackjack.<br />');
	$PHP_OUTPUT.=('You have lost ' . $db->getField('blackjack_lose') . ' credits from Blackjack.<br />');
	$PHP_OUTPUT.=('You have won ' . $db->getField('lotto') . ' credits from the lotto.<br />');
	$PHP_OUTPUT.=('You have had ' . $db->getField('drinks') . ' drinks at the bar.<br />');
	$PHP_OUTPUT.=('You have bought ' . $db->getField('mines') . ' mines.<br />');
	$PHP_OUTPUT.=('You have bought ' . $db->getField('combat_drones') . ' combat_drones.<br />');
	$PHP_OUTPUT.=('You have bought ' . $db->getField('scout_drones') . ' scout_drones.<br />');
	$PHP_OUTPUT.=('You have has gained ' . $db->getField('money_gained') . ' credits from killing.<br />');
	$PHP_OUTPUT.=('You have has killed ' . $db->getField('killed_ships') . ' credits worth of ships.<br />');
	$PHP_OUTPUT.=('You have has lost ' . $db->getField('died_ships') . ' credits worth of ships.<br />');

}

//current game stats
$PHP_OUTPUT.=('<br /><br />');
if (empty($player)) $game_id = 0;
else $game_id = $player->getGameID();
$db->query('SELECT * FROM player_has_stats WHERE account_id = '.$account->account_id.' AND game_id = '.$game_id);
if ($db->nextRecord()) {

	$PHP_OUTPUT.=('<b>Current Game Extended Stats</b><br />');
	$PHP_OUTPUT.=('You have busted ' . $db->getField('planet_busts') . ' planets.<br />');
	$PHP_OUTPUT.=('You have busted a total of ' . $db->getField('planet_bust_levels') . ' combined levels on planets.<br />');
	$PHP_OUTPUT.=('You have raided ' . $db->getField('port_raids') . ' ports.<br />');
	$PHP_OUTPUT.=('You have raided a total of ' . $db->getField('port_raid_levels') . ' combined levels of ports.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('planet_damage') . ' damage to planets.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('port_damage') . ' damage to ports.<br />');
	$PHP_OUTPUT.=('You have explored ' . $db->getField('sectors_explored') . ' sectors.<br />');
	$PHP_OUTPUT.=('You have died ' . $db->getField('deaths') . ' times.<br />');
	$PHP_OUTPUT.=('You have traded ' . $db->getField('goods_traded') . ' goods.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('bounties_claimed') . ' bounties.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('bounty_amount_claimed') . ' credits from bounties.<br />');
	$PHP_OUTPUT.=('You have claimed ' . $db->getField('military_claimed') . ' credits from military payment.<br />');
	$PHP_OUTPUT.=('You have had a total of ' . $db->getField('bounty_amount_on') . ' credits bounty placed on you.<br />');
	$PHP_OUTPUT.=('You have done ' . $db->getField('player_damage') . ' damage to other ships.<br />');
	$PHP_OUTPUT.=('The total experience of traders you have killed is ' . $db->getField('traders_killed_exp') . '.<br />');
	$PHP_OUTPUT.=('You have gained ' . $db->getField('kill_exp') . ' experience from killing other traders.<br />');
	$PHP_OUTPUT.=('You have used ' . $db->getField('turns_used') . ' turns since your last death.<br />');
	$PHP_OUTPUT.=('You have won ' . $db->getField('blackjack_win') . ' credits from Blackjack.<br />');
	$PHP_OUTPUT.=('You have lost ' . $db->getField('blackjack_lose') . ' credits from Blackjack.<br />');
	$PHP_OUTPUT.=('You have won ' . $db->getField('lotto') . ' credits from the lotto.<br />');
	$PHP_OUTPUT.=('You have had ' . $db->getField('drinks') . ' drinks at the bar.<br />');

}

?>
