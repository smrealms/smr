<?php
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('SmrPort.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

if ($var['func'] == 'Map') {

	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE account_id = '.$account_id.' AND game_id = '.$game_id);

	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' ORDER BY sector_id');
	while ($db->nextRecord())
	{
		SmrPort::getPort($game_id,$db->getField('sector_id'))->addCachePort($account_id);
	}

}
elseif ($var['func'] == 'Money')
	$player->setCredits(50000000);
elseif ($var['func'] == 'PageNewb')
{
	if(!defined('ACCOUNT_PAGE'))
	{
		create_error('You\'re so mean! Go pick on someone else!');
	}
	$page =& SmrPlayer::getPlayer(ACCOUNT_PAGE,$player->getGameID());
	$page->setNewbieTurns(0);
}
elseif ($var['func'] == 'Ship')// && $_REQUEST['ship_id'] <= 75 && $_REQUEST['ship_id'] != 68)
{
	$ship_id = $_REQUEST['ship_id'];
	
	$speed = $ship->getSpeed();
	// assign the new ship
	$ship->decloak();
	$ship->disableIllusion();
	$ship->setShipTypeID($ship_id);
	
	//now adapt turns
	$player->setTurns($player->getTurns() * ($speed / $ship->getSpeed()));
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
	$sector_to = trim($_REQUEST['sector_to']);
	if(!is_numeric($sector_to))
	{
		create_error('Sector ID has to be a number.');
	}
	if(SmrGalaxy::getGalaxyContaining($player->getGameID(), $sector_to))
	{
		create_error('Sector ID is not in any galaxy.');
	}
	$player->setSectorID($sector_to);
	$player->setLandedOnPlanet(false);
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
	$ship->removeAllWeapons();
elseif ($var['func'] == 'Hard_add') {
	$type_hard = $_REQUEST['type_hard'];
	$amount_hard = $_REQUEST['amount_hard'];
	$db->query('REPLACE INTO ship_has_hardware (account_id,game_id,hardware_type_id,amount,old_amount) VALUES ('.$player->getAccountID().','.$player->getGameID().','.$type_hard.','.$db->escapeString($amount_hard).','.$db->escapeString($amount_hard).')');
} elseif ($var['func'] == 'Relations') {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	$player->setRelations($amount,$race);
} elseif ($var['func'] == 'Race_Relations') {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	if(!is_numeric($amount) || !is_numeric($race))
		create_error('Amount and Race IDs have to be numbers.');
	$db->query('UPDATE race_has_relation SET relation = '.$amount.' WHERE race_id_1 = '.$player->getRaceID().' AND race_id_2 = '.$race.' AND game_id = '.$player->getGameID());
	$db->query('UPDATE race_has_relation SET relation = '.$amount.' WHERE race_id_1 = '.$race.' AND race_id_2 = '.$player->getRaceID().' AND game_id = '.$player->getGameID());
} elseif ($var['func'] == 'Race') {
	$race = $_REQUEST['race'];
	if(!is_numeric($race))
		create_error('Amount and Race IDs have to be numbers.');
	$player->setRaceID($race);
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