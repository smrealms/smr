<?php

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you can\'t land on a planet.');
}

// do we have enough turns?
if ($player->getTurns() == 0) {
	create_error('You don\'t have enough turns to land on planet.');
}

if($player->hasNewbieTurns()) {
	create_error('You cannot land on a planet whilst under newbie protection.');
}

if ($player->hasAlliance()) {
	$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
	if ($db->nextRecord()) {
		$role_id = $db->getField('role_id');
	}
	else {
		$role_id = 0;
	}
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = '.$db->escapeNumber($role_id));
	$db->nextRecord();
	if (!$db->getBoolean('planet_access')) {
		$db->query('SELECT owner_id FROM planet WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');
		if ($db->nextRecord() && $db->getInt('owner_id') != 0 && $db->getInt('owner_id') != $player->getAccountID()) {
			create_error('Your alliance doesn\'t allow you to dock at their planet.');
		}
	}
}
$player->setLandedOnPlanet(true);
$player->takeTurns(1,1);
$account->log(LOG_TYPE_MOVEMENT, 'Player lands at planet', $player->getSectorID());
forward(create_container('skeleton.php', 'planet_main.php'));

?>