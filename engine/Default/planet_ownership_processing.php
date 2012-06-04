<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
// get a planet from the sector where the player is in
$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$player->getSectorID());
include(get_file_loc('planet_claim_disallow.php'));
$action = $_REQUEST['action'];
$password = $_REQUEST['password'];
$name = $_REQUEST['name'];

if ($action == 'Take Ownership')
{
	if ($planet->hasOwner() && $planet->getPassword() != $password)
		create_error('You are not allowed to take ownership!');

	// delete all previous ownerships
	$db->query('UPDATE planet SET owner_id = 0, password = NULL ' .
							 'WHERE owner_id = '.$player->getAccountID().' AND ' .
							 		'game_id = '.$player->getGameID());

	// set ownership
	$planet->setOwnerID($player->getAccountID());
	$planet->removePassword();
	$planet->update();
	$account->log(11, 'Player takes ownership of planet.', $player->getSectorID());
}
else if ($action == 'Rename')
{
	include(get_file_loc('planet_change_name.php'));
	// rename planet
	$planet->setName($name);
	$planet->update();
	$account->log(11, 'Player renames planet to '.$name.'.', $player->getSectorID());

}
else if ($action == 'Set Password')
{
	// set password
	$planet->setPassword($password);
	$planet->update();
	$account->log(11, 'Player sets planet password to '.$password, $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_ownership.php'));

?>