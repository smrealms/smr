<?php
		require_once(get_file_loc("smr_planet.inc"));
$planet = new SMR_PLANET($player->sector_id, $player->game_id);

$planet_player = new SMR_PLAYER($var["account_id"], SmrSession::$game_id);
$owner = new SMR_PLAYER($planet->owner_id, $player->game_id);
if ($owner->alliance_id != $player->alliance_id)
	create_error("You can not kick someone off a planet your alliance does not own!");
$message = "You have been kicked from $planet->planet_name in #$player->sector_id";
$player->send_message($planet_player->account_id, 2, format_string($message, false));

$planet_player->land_on_planet = "FALSE";
//update their last active time so that they are visable if kicked
$worst = time() - 1500;
if ($planet_player->last_active < $worst)
	$planet_player->last_active = $worst;
$planet_player->update();

forward(create_container("skeleton.php", "planet_main.php"));

?>