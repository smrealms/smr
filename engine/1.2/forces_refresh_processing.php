<?php
		require_once(get_file_loc("smr_force.inc"));
$forces	= new SMR_FORCE($var["owner_id"], $player->sector_id, $player->game_id);

if($forces->combat_drones == 0 && $forces->mines == 0 && $forces->scout_drones == 1) {
	$days = 2;
}
else {
	$days = ceil(($forces->combat_drones + $forces->scout_drones + $forces->mines) / 10);
}
if ($days > 5) $days = 5;
$forces->expire = time() + ($days * 86400);
$forces->update();

forward(create_container("skeleton.php", "current_sector.php"));

?>
