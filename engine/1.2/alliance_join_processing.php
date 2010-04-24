<?php

require_once(get_file_loc('smr_alliance.inc'));

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated != "TRUE")
	create_error("You are not validated. You can't join an alliance yet.");

// ********************************
// *
// * B e g i n
// *
// ********************************

$alliance = new SMR_ALLIANCE($var["alliance_id"], SmrSession::$game_id);
$password = $_REQUEST['password'];

if ($password != $alliance->password)
	create_error("Incorrect Password!");

// assign the player to the current alliance
$player->alliance_id = $alliance->alliance_id;
$player->update();
$db->query("INSERT INTO player_has_alliance_role (game_id, account_id, role_id,alliance_id) VALUES ($player->game_id, $player->account_id, 2,$alliance_id)");
$account->log(3, "joined alliance: $alliance->alliance_name", $player->sector_id);

forward(create_container("skeleton.php", "alliance_roster.php"));

?>