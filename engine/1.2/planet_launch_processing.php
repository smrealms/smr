<?php

$player->land_on_planet = "FALSE";
$player->update();
$account->log(11, "Player launches from planet", $player->sector_id);
forward(create_container("skeleton.php", "current_sector.php"));

?>