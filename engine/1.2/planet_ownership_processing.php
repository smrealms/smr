<?php
		require_once(get_file_loc("smr_planet.inc"));
// get a planet from the sector where the player is in
$planet = new SMR_PLANET($player->sector_id, SmrSession::$game_id);
include(get_file_loc("planet_claim_disallow.php"));
$action = $_REQUEST['action'];
$password = $_REQUEST['password'];
$name = $_REQUEST['name'];

if ($action == "Take Ownership") {

	if ($planet->owner_id != 0 && $planet->password != $password)
		create_error("You are not allowed to take ownership!");

	// delete all previous ownerships
	$db->query("UPDATE planet SET owner_id = 0, password = NULL " .
							 "WHERE owner_id = $player->account_id AND " .
							 		"game_id = $player->game_id");

	// set ownership
	$planet->owner_id = $player->account_id;
	$planet->password = "";
	$planet->update();
	$account->log(11, "Player takes ownership of planet.", $player->sector_id);

} else if ($action == "Rename") {

	include(get_file_loc('planet_change_name.php'));
	// rename planet
	$planet->planet_name = $name;
	$planet->update();
	$account->log(11, "Player renames planet to $name.", $player->sector_id);

} else if ($action == "Set Password") {

	// set password
	$planet->password = $password;
	$planet->update();
	$account->log(11, "Player sets planet password to $password", $player->sector_id);

}

forward(create_container("skeleton.php", "planet_ownership.php"));

?>