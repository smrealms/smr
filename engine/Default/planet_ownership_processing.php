<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
// get a planet from the sector where the player is in
$planet = $player->getSectorPlanet();
$action = Request::get('action');

if ($action == 'Take Ownership') {
	if ($planet->hasOwner() && $planet->getPassword() != Request::get('password')) {
		create_error('You are not allowed to take ownership!');
	}

	// delete all previous ownerships
	$db->query('UPDATE planet SET owner_id = 0, password = NULL
				WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));

	// set ownership
	$planet->setOwnerID($player->getAccountID());
	$planet->removePassword();
	$planet->update();
	$account->log(LOG_TYPE_PLANETS, 'Player takes ownership of planet.', $player->getSectorID());
} elseif ($action == 'Rename') {
	$name = trim(Request::get('name'));
	if (empty($name)) {
		create_error('You cannot leave your planet nameless!');
	}
	// rename planet
	$planet->setName($name);
	$planet->update();
	$account->log(LOG_TYPE_PLANETS, 'Player renames planet to ' . $name . '.', $player->getSectorID());

} elseif ($action == 'Set Password') {
	// set password
	$password = Request::get('password');
	$planet->setPassword($password);
	$planet->update();
	$account->log(LOG_TYPE_PLANETS, 'Player sets planet password to ' . $password, $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_ownership.php'));
