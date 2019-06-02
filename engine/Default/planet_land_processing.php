<?php

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you can\'t land on a planet.');
}

// do we have enough turns?
if ($player->getTurns() == 0) {
	create_error('You don\'t have enough turns to land on planet.');
}

if ($player->hasNewbieTurns()) {
	create_error('You cannot land on a planet whilst under newbie protection.');
}

//check to make sure the planet isn't full!
$planet = $player->getSectorPlanet();
if ($planet->getMaxLanded() != 0 && $planet->getMaxLanded() <= $planet->countPlayers()) {
	create_error('You cannot land because the planet is full!');
}

if ($player->hasAlliance()) {
	$role_id = $player->getAllianceRole();
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
	$db->nextRecord();
	if (!$db->getBoolean('planet_access')) {
		if ($planet->hasOwner() && $planet->getOwnerID() != $player->getAccountID()) {
			create_error('Your alliance doesn\'t allow you to dock at their planet.');
		}
	}
}
$player->setLandedOnPlanet(true);
$player->takeTurns(1, 1);
$account->log(LOG_TYPE_MOVEMENT, 'Player lands at planet', $player->getSectorID());
forward(create_container('skeleton.php', 'planet_main.php'));
