<?php
$action = $_REQUEST['action'];
if ($action == 'Yes!') {

	$db->query("UPDATE player SET newbie_turns = 0, " .
								 "newbie_warning = 'FALSE' " .
			   "WHERE account_id = ".SmrSession::$old_account_id." AND " .
					 "game_id = ".SmrSession::$game_id);

}
if ($player->land_on_planet == "TRUE")
	$area = "planet_main.php";
else
	$area = "current_sector.php";
$account->log(5, "Player drops newbie turns.", $player->sector_id);
forward(create_container("skeleton.php", "$area"));

?>