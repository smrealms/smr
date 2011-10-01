<?php

// is account validated?
if (!$account->isValidated())
	create_error('You are not validated so you can\'t land on a planet.');

// do we have enough turns?
if ($player->getTurns() == 0)
	create_error('You don\'t have enough turns to land on planet.');
	
if ($player->hasAlliance())
{
	$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$player->getAllianceID());
	if ($db->nextRecord()) $role_id = $db->getField('role_id');
	else $role_id = 0;
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
	$db->nextRecord();
	if ($db->getField('planet_access') == 'FALSE')
	{
		$db->query('SELECT owner_id FROM planet WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID().' LIMIT 1');
		$db->nextRecord();
		if ($db->getField('owner_id') != 0 && $db->getField('owner_id') != $player->getAccountID())
			create_error('Your alliance doesn\'t allow you to dock at their planet.');
	}
}
$player->setLandedOnPlanet(true);
$player->takeTurns(1,1);
$account->log(11, 'Player lands at planet', $player->getSectorID());
forward(create_container('skeleton.php', 'planet_main.php'));

?>