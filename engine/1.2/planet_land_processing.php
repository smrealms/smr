<?php

// is account validated?
if ($account->validated == "FALSE")
	create_error("You are not validated so you can't land on a planet.");

// do we have enough turns?
if ($player->turns == 0)
	create_error("You don't have enough turns to land on planet.");
	
if ($player->alliance_id > 0) {
	$db->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
	if ($db->next_record()) $role_id = $db->f("role_id");
	else $role_id = 0;
	$db->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND role_id = $role_id");
	$db->next_record();
	if (!$db->f("planet_access")) {
		$db->query("SELECT owner_id FROM planet WHERE sector_id = $player->sector_id AND game_id = $player->game_id LIMIT 1");
		$db->next_record();
		if ($db->f("owner_id") != $player->account_id)
			create_error("Your alliance doesn't allow you to dock at their planet");
	}
}
$player->land_on_planet = "TRUE";
$player->take_turns(1);
$player->update();
$account->log(11, "Player lands at planet", $player->sector_id);
forward(create_container("skeleton.php", "planet_main.php"));

?>