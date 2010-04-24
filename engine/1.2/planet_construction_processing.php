<?php
		require_once(get_file_loc("smr_planet.inc"));
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
$action = $_REQUEST['action'];
if ($action == "Build") {

	if ($planet->build())
		create_error("There is already a building in progress!");

	$player->credits -= $var["cost"];
	$player->update();

	// now start the construction
	$planet->start_construction($var["construction_id"]);

	$db->query("SELECT * FROM planet_construction WHERE construction_id = " . $var["construction_id"]);
	$db->next_record();
	$name = $db->f("construction_name");
	$account->log(11, "Player starts a $name on planet.", $player->sector_id);

} elseif ($action == "Cancel") {

	$db->query("DELETE FROM planet_build_construction WHERE sector_id = $player->sector_id AND game_id = $player->game_id");
	$account->log(11, "Player cancels planet construction", $player->sector_id);

}

forward(create_container("skeleton.php", "planet_construction.php"));

?>