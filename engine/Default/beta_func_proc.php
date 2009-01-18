<?
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('SmrPort.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

if ($var['func'] == 'Map') {

	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE account_id = '.$account_id.' AND game_id = '.$game_id);
	$db->query('DELETE FROM player_visited_port WHERE account_id = '.$account_id.' AND game_id = '.$game_id);

	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' ORDER BY sector_id');
	while ($db->nextRecord())
	{
		SmrPort::getPort($game_id,$db->getField('sector_id'))->addCachePort($account_id);
	}

} elseif ($var['func'] == 'Money')
	$player->setCredits(50000000);
elseif ($var['func'] == 'Ship' && $_REQUEST['ship_id'] <= 75 && $_REQUEST['ship_id'] != 68) {
	$ship_id = $_REQUEST['ship_id'];
	
	// assign the new ship
	$ship->setShipTypeID($ship_id);
	// update
	$ship->update();

	$player->setShipTypeID($ship_id);
	//check for more weapons than allowed
	$db->query('SELECT * FROM ship_type WHERE ship_type_id = '.$ship_id);
	$db->nextRecord();
	$max_weps = $db->getField('hardpoint');
	$speed = $db->getField('speed');
	$db->query('SELECT * FROM ship_has_weapon WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	if ($db->getNumRows() > $max_weps) {
		$extra = $db->getNumRows() - $max_weps;
		for ($i=1; $i <= $extra; $i++) {
			$db->query('SELECT * FROM ship_has_weapon WHERE account_id = '.$player->getAccountID().' ORDER BY order_id DESC');
			$db->nextRecord();
			$order_id = $db->getField('order_id');
			$db->query('DELETE FROM ship_has_weapon WHERE account_id = '.$player->getAccountID().' AND order_id = '.$order_id);
		}
	}
	//now adapt turns
	$turns = $player->getTurns() * ($speed / $ship->getSpeed());
	if ($turns > $player->getMaxTurns()) $turns = $player->getMaxTurns();
	$player->setTurns($turns);
	$player->update();
	//now make sure they don't have extra hardware
	$db->query('DELETE FROM ship_is_cloaked WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	$db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	doUNO($player,$ship);	
	
} elseif ($var['func'] == 'Weapon') {
	$weapon_id = $_REQUEST['weapon_id'];
	$amount = $_REQUEST['amount'];
	for ($i = 1; $i <= $amount; $i++)
	{
		$ship->addWeapon($weapon_id);
	}

} elseif ($var['func'] == 'Uno') {

	doUNO($player,$ship);

} elseif ($var['func'] == 'Warp') {
	$sector_to = $_REQUEST['sector_to'];
	$player->setSectorID($sector_to);
} elseif ($var['func'] == 'Turns') {
	$player->setTurns($_REQUEST['turns']);
} elseif ($var['func'] == 'Exp') {
	$exp = $_REQUEST['exp'];
	if ($exp > 500000) $exp = 500000;
	$player->setExperience($exp);
} elseif ($var['func'] == 'Align'){
	$align = $_REQUEST['align'];
	if($align > 500) $align=500;
	else if($align<-500) $align=-500;
	$player->setAlignment($align);
} elseif ($var['func'] == 'Kills') {
	$kills = $_REQUEST['kills'];
	$db->query('UPDATE account_has_stats SET kills = '.$db->escapeString($kills).' WHERE account_id = '.$player->getAccountID());
} elseif ($var['func'] == 'Traded_XP') {
	$traded_xp = $_REQUEST['traded_xp'];
	$db->query('UPDATE account_has_stats SET experience_traded = '.$db->escapeString($traded_xp).' WHERE account_id = '.$player->getAccountID());
} elseif ($var['func'] == 'RemWeapon')
	$db->query('DELETE FROM ship_has_weapon WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());
elseif ($var['func'] == 'Hard_add') {
	$type_hard = $_REQUEST['type_hard'];
	$amount_hard = $_REQUEST['amount_hard'];
	$db->query('REPLACE INTO ship_has_hardware (account_id,game_id,hardware_type_id,amount,old_amount) VALUES ('.$player->getAccountID().','.$player->getGameID().','.$type_hard.','.$db->escapeString($amount_hard).','.$db->escapeString($amount_hard).')');
} elseif ($var['func'] == 'Relations') {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	$db->query('UPDATE player_has_relation SET relation = '.$db->escapeString($amount).' WHERE race_id = '.$race.' AND account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
} elseif ($var['func'] == 'Race_Relations') {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	$db->query('UPDATE race_has_relation SET relation = '.$amount.' WHERE race_id_1 = '.$player->getRaceID().' AND race_id_2 = '.$race.' AND game_id = '.$player->getGameID());
	$db->query('UPDATE race_has_relation SET relation = '.$amount.' WHERE race_id_1 = '.$race.' AND race_id_2 = '.$player->getRaceID().' AND game_id = '.$player->getGameID());
}
$container['url'] = 'skeleton.php';
$container['body'] = $var['body'];
forward($container);


function doUNO(&$player,&$ship)
{
	$maxHardware = $ship->getMaxHardware();
	foreach($maxHardware as $key => $max)
		$ship->setHardware($key,$max);
}
?>