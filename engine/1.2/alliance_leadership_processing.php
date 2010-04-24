<?php
$leader_id = $_REQUEST['leader_id'];
$db->query("UPDATE alliance SET leader_id = $leader_id " .
							"WHERE alliance_id = $player->alliance_id AND " .
								  "game_id = $player->game_id");
$db->query("UPDATE player_has_alliance_role SET role_id = 2 WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
$db->query("UPDATE player_has_alliance_role SET role_id = 1 WHERE account_id = $leader_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
forward(create_container("skeleton.php", "alliance_roster.php"));

?>